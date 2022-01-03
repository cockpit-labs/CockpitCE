<?php
/*
 * Core
 * QuestionnaireDataTransformer.php
 *
 * Copyright (c) 2020 Sentinelo
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
use App\Entity\Media\Media;
use App\Entity\Questionnaire\Questionnaire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints as Assert;

final class QuestionnaireDataTransformer extends CommonDataProvider implements DataTransformerInterface
{
    /**
     * @param \App\Entity\Questionnaire\Questionnaire $questionnaire
     *
     * @return \App\Entity\Questionnaire\Questionnaire
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    private function sendPDF(Questionnaire $questionnaire): Questionnaire
    {

        if (empty($this->getRequest()->get('recipients'))) {
            throw new BadRequestHttpException();
        }

        $email = new Email();

        $recipes         = $this->getRequest()->get('recipients');
        $emailConstraint = new Assert\Email();

        foreach ($recipes as $recipient) {
            $errors = $this->getValidator()->validate($recipient, $emailConstraint);
            if (count($errors) !== 0) {
                // it is not a email
                $user = $this->getKeycloakConnector()->getUser($recipient);
                if (empty($user)) {
                    throw new NotFoundHttpException("Unknown user or email address " . $recipient);
                } else {
                    $email->addTo($user['email']);
                }
            } else {
                $email->addTo($recipient);
            }
        }

        // get PDF file
        $pdfMedia = $questionnaire->getPdf();
        $media    = $this->getEntityManager()
                         ->getRepository(Media::class)
                         ->find($pdfMedia->getId());

        if (!$media) {
            throw new NotFoundHttpException(
                'No pdf found ');
        }
        $mimetype = $media->getMimetype();
        $pdf      = stream_get_contents($this->getStorage()->resolveStream($media, 'file'));

        $from   = $this->getGlobals()->getParam('CKP_EMAILFROM', 'cockpit@cockpitlabs.io');
        $cc     = $this->getKeycloakConnector()->getUser($questionnaire->getCreatedBy())['email'];
        $errors = $this->getValidator()->validate($cc, $emailConstraint);
        if (count($errors) === 0) {
            $email->addCc($cc);
        }
        $email->from($from)
              ->attach($pdf, "pdf file", $mimetype)
              ->text($questionnaire->getDescription())
              ->subject($questionnaire->getLabel());

        $this->getMailer()->send($email);
        return $questionnaire;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($questionnaire, string $to, array $context = [])
    {
        if (empty($this->getUser())) {
            return $questionnaire;
        }

        switch ($context[$context['operation_type'] . '_operation_name']) {
            case 'sendpdf':
                $questionnaire = $this->sendPDF($questionnaire);
                break;

            default:
                break;
        }
        return $questionnaire;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if (Questionnaire::class !== $to) {
            // just transform Questionnaire
            return false;
        }
        if ($data instanceof Questionnaire
            && ($context['output']['class'] ?? null) !== null
            && in_array($context[$context['operation_type'] . '_operation_name'], ['sendpdf'])) {
            // it's input data
            // as it's json, we can change things
            // if it's an object, its already transformed

            return true;
        }
        return false;

    }
}
