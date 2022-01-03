<?php
/*
 * Core
 * GetQuestionnairePDFContentAction.php
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

namespace App\Controller;

use App\Entity\Media\QuestionnairePDFMedia;
use App\Entity\Questionnaire\Questionnaire;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Storage\StorageInterface;

final class GetQuestionnairePDFContentAction extends GetMediaContentAction
{
    /**
     * GetQuestionnairePDFContentAction constructor.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {

        $this->setMediaClass(QuestionnairePDFMedia::class);
        $this->setDownload(false);
        parent::__construct($storage);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function __invoke(Request $request): Response
    {
        // it's a Questionnaire id
        // we need to find the QuestionnairePdfMedia id

        $id = $request->get('id');
        $this->setDownload($request->get('download', 'false') != 'false');
        $questionnaire = $this->getDoctrine()->getRepository(Questionnaire::class)->find($id);
        if (empty($questionnaire)) {
            throw new NotFoundHttpException('No questionnaire found for id ' . $id);
        }
        if (empty($questionnaire->getPdf())) {
            throw new NotFoundHttpException('No questionnaire pdf found ' );
        }

        $this->setId($questionnaire->getPdf()->getId());

        return parent::__invoke($request);
    }
}
