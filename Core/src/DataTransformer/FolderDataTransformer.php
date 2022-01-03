<?php
/*
 * Core
 * FolderDataTransformer.php
 *
 * Copyright (c) 2021 Sentinelo
 *
 * @author  Christophe AGNOLA
 * @license MIT License (https://mit-license.org)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 * NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\DataProvider\CommonDataProvider;
use App\Entity\Folder\Folder;
use App\Entity\Group;
use App\Entity\Media\Media;
use App\Entity\Media\MediaOwner;
use App\Entity\Media\QuestionnairePDFMedia;
use App\Entity\Media\UserMedia;
use App\Entity\Permission;
use App\Entity\Right;
use App\Entity\User;
use App\Service\ApplicationGlobals;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;
use Twig\TwigFunction;

final class FolderDataTransformer extends CommonDataProvider implements DataTransformerInterface
{
// ToDo: move email sending in background (messenger)
// ToDo: move pdf generation in background (messenger)

    /**
     * @var array
     */
    private array $context;

    /**
     * @var \App\Entity\Folder\Folder
     */
    private Folder $folder;

    /**
     * @param \App\Entity\Media\Media $media
     * @param array                   $owners
     */
    private function addMediaOwner(?Media $media, array $owners)
    {
        if (empty($media)) {
            return;
        }
        $repo = $this->getEntityManager()->getRepository(MediaOwner::class);
        foreach ($owners as $owner) {
            $mediaOwner = new MediaOwner();
            $mediaOwner->setMedia($media);
            $mediaOwner->setOwner($owner);
            if (!$repo->exists($mediaOwner)) {
                $this->getEntityManager()->persist($mediaOwner);
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param \App\Entity\Folder\Folder $data
     *
     * @return \App\Entity\Folder\Folder
     * @throws \Exception
     */
    private function create(): void
    {
        $groupId = ApplicationGlobals::getIdFromIri($this->folder->getappliedTo());
        $group   = $this->getEntityManager()->getRepository(Group::class)->find($groupId);
        if (empty($group)) {
            throw new NotFoundHttpException("Group not found");
        }

        // check if occurences not over limit
        $inProgressFoldersCount = $this->getEntityManager()
                                       ->getRepository(Folder::class)
                                       ->perCurrentPeriodCount($this->folder->getFolderTpl(), $group);
        $maxFolders             = $this->folder->getFolderTpl()->getMaxFolders();
        if ($inProgressFoldersCount >= $maxFolders) {
            throw new NotAcceptableHttpException("Folders count off limit for period");
        }

        // get parents
        $parents   = $group->getIdPath();
        $folderTpl = $this->folder->getFolderTpl();
        $this->getEntityManager()->initializeObject($folderTpl);
        if (empty($folderTpl)) {
            throw new NotFoundHttpException("Folder Template not found");
        }

        $this->folder   = $folderTpl->instantiate();

        foreach ($this->folder->getCalendars() as $calendar) {

            $this->folder->setPeriodEnd($calendar->getPeriodEnd());
            // periodStart cannot be null, so force it to first end value, if it is null
            $this->folder->setPeriodStart($calendar->getPeriodStart());
        }
        $this->folder->setCreatedBy($this->getUser()->getUsername())
                     ->setUpdatedBy($this->getUser()->getUsername())
                     ->setappliedTo($groupId)
                     ->setParentGroups($parents);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \TheCodingMachine\Gotenberg\ClientException
     * @throws \TheCodingMachine\Gotenberg\FilesystemException
     * @throws \TheCodingMachine\Gotenberg\RequestException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function generatePDF(): void
    {
        if (!empty($this->getRequest()->get('nopdf'))) {
            return;
        }

        $selectedChoices = new TwigFunction('getSelectedChoices', function ($question) {
            $selectedChoicesLabels = [];
            if (!empty($question['answers'])) {
                $selectedChoicesLabels = array_map(function ($answer) {
                    return $answer['choice']['label'] ?? '';
                }, $question['answers']);
            }

            return $selectedChoicesLabels;
        });

        $this->getTwig()->addFunction($selectedChoices);

        $photoLibs = [];
        $photoIri  = str_replace('/', '\/',
                                 $this->getGlobals()->getIriConverter()->getIriFromResourceClass(UserMedia::class) . '/');
        foreach ($this->folder->getQuestionnaires() as $questionnaire) {
            // check all photos in answers
            foreach ($questionnaire->getBlocks() as $block) {
                foreach ($block->getQuestions() as $question) {
                    foreach ($question->getAnswers() as $answer) {
                        if (!empty($answer->getMedia())) {
                            $stream = $this->getStorage()->resolveStream($answer->getMedia(),
                                                                         'file');

                            $photoLibs[$photoIri . $answer->getMedia()->getId()] = base64_encode(stream_get_contents($stream));
                        }
                    }
                    foreach ($question->getPhotos() as $photo) {
                        $stream = $this->getStorage()->resolveStream($photo, 'file');

                        $photoLibs[$photoIri . $photo->getId()] = base64_encode(stream_get_contents($stream));
                    }
                }
            }
            // generate PDF
            $this->context['groups'][]         = "Label";
            $this->context['groups'][]         = "Description";
            $this->context['groups'][]         = "Folder:Read";
            $this->context['groups'][]         = "Timestamp";
            $this->context['groups'][]         = "Blame";
            $this->context['skip_null_values'] = false;

            $data = $this->getNormalizer()->normalize($questionnaire, null, $this->context);

            // replace photo IRI with base64 image
            $datajson = json_encode($data);
            foreach ($photoLibs as $iri => $base64) {
                $datajson = str_replace($iri, $base64, $datajson);
            }
            $data = json_decode($datajson, true);

            $client = new Client($this->getGlobals()->getGotenbergUrl(), new \Http\Adapter\Guzzle6\Client());
            $user   = $this->folder->getCreatedBy();
            $repo   = $this->getEntityManager()->getRepository(User::class);
            $user   = $repo->findBy(['username' => $user]);

            $user = $this->getNormalizer()->normalize($user[0]);

            // render data with twig

            $htmlData = $this->getTwig()->render('questionnaire.html.twig',
                                                 [
                                                     'questionnaire' => $data,
                                                     'user'          => $user,
                                                     'locale'        => 'en'
                                                 ]);

            $html    = DocumentFactory::makeFromString('qpdf.html', $htmlData);
            $request = new HTMLRequest($html);
            $request->setMargins(Request::NO_MARGINS);

            $pdfTempFile = $this->getKernel()->getLocalTmpDir() . '/QPDF-' . $questionnaire->getId() . '.pdf';
            $client->store($request, $pdfTempFile);

            // create an 'uploaded' PDF
            $pdfFile = new UploadedFile($pdfTempFile, 'Q' . $questionnaire->getId() . '.pdf', 'application/pdf',
                                        filesize($pdfTempFile), true);

            // prepare new PDF
            $pdf = new QuestionnairePDFMedia();
            $pdf->setFile($pdfFile);

            $oldPdf = $questionnaire->getPdf();

            // set new PDF document, and remove old one
            $questionnaire->setPdf($pdf);
            if ($oldPdf && !empty($this->getEntityManager()->find(Media::class, $oldPdf->getId()))) {
                $this->getEntityManager()->remove($oldPdf);
            }
            $this->getEntityManager()->persist($pdf);
            $this->getEntityManager()->flush();

            // force old PDF Document removal (hardDelete) by removing a second time
            if ($oldPdf && !empty($this->getEntityManager()->find(Media::class, $oldPdf->getId()))) {
                $this->getEntityManager()->remove($oldPdf);
                $this->getEntityManager()->persist($pdf);
                $this->getEntityManager()->flush();
            }
            unlink($pdfTempFile);
        }
    }

    /**
     * @param $rightId
     *
     * @return \App\Entity\Right
     */
    private function getRight($rightId): Right
    {
        return $this->getEntityManager()->getRepository(Right::class)->find($rightId);
    }

    /**
     * @return bool
     */
    private function isDeletable(): bool
    {
        return $this->folder->getState() === 'DRAFT';
    }


    /**
     *
     */
    private function owningPhotosUpdate(): void
    {
        // create or update photo owning information
        foreach ($this->folder->getQuestionnaires() as $questionnaire) {
            foreach ($questionnaire->getBlocks() as $block) {
                foreach ($block->getQuestions() as $question) {
                    $owners = [
                        $this->folder->getappliedTo(),
                        $question->getId(),
                        $block->getId(),
                        $this->folder->getId(),
                        $this->folder->getFolderTpl()->getId()
                    ];
                    foreach ($question->getAnswers() as $answer) {
                        if (!empty($answer->getMedia())) {
                            $this->addMediaOwner($answer->getMedia(), $owners);
                            $answer->getMedia()->setTarget($this->folder->getappliedTo());
                            $answer->getMedia()->setFolder($this->folder);
                        }
                    }
                    foreach ($question->getPhotos() as $photo) {
                        $this->addMediaOwner($photo, $owners);
                        if (!empty($this->folder->getappliedTo())) {
                            $photo->setTarget($this->folder->getappliedTo());
                            $photo->setFolder($this->folder);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array|object $data
     * @param string       $to
     * @param array        $context
     *
     * @return bool
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // transform only Folder creation,update and get
        if (Folder::class !== $to) {
            return false;
        }

        if (($context['input']['class'] ?? null) !== null
            && in_array($context[$context['operation_type'] . '_operation_name'],
                        [
                            'precreate',
                            'create',
                            'delete',
                            'put',
                            'validate',
                        ])) {
            return true;
        }

        return false;
    }

    /**
     * @param object $data
     * @param string $to
     * @param array  $context
     *
     * @return \App\Entity\Folder\Folder|object
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \TheCodingMachine\Gotenberg\ClientException
     * @throws \TheCodingMachine\Gotenberg\FilesystemException
     * @throws \TheCodingMachine\Gotenberg\RequestException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function transform($data, string $to, array $context = [])
    {
        $this->folder        = $data;
        $this->context       = $context;
        switch ($context[$context['operation_type'] . '_operation_name']) {
            case 'create':
                $this->context['groups'][] = "Label";
                $this->context['groups'][] = "Description";
                $this->create();
                break;

            case 'validate':
                $this->folder->processScore();
                $this->owningPhotosUpdate();
                $this->generatePDF();
                $this->folder->setState(('VALIDATED'));
                break;

            default:
                // what?
                // an unknown operation?
                break;
        }
        return $this->folder;
    }
}
