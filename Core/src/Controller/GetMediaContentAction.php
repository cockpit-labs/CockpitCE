<?php
/*
 * Core
 * GetMediaContentAction.php
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

use App\Entity\Media\Media;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Storage\StorageInterface;
use function fopen;
use function stream_copy_to_stream;

abstract class GetMediaContentAction extends AbstractController
{
    /**
     * @var string
     */
    private $mediaClass = "";

    /**
     * @var bool
     */
    private $download = false;

    /**
     * @var string
     */
    private $id = '';
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    private StorageInterface $storage;

    /**
     * GetMediaContentAction constructor.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function __invoke(Request $request): Response
    {
        if (empty($this->getId())) {
            $this->setId($request->get('id'));
        }
        $media = $this->getDoctrine()
                      ->getRepository($this->mediaClass)
                      ->find($this->getId());

        if (!$media) {
            throw $this->createNotFoundException(
                'No media found for id ' . $this->getId()
            );
        }
        $response = null;
        if ($media->getMimetype() === 'application/pdf') {
            $response = $this->pdfContent($media);
        } else {
            $response = $this->defaultContent($media);
        }
        return $response;
    }

    /**
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function defaultContent(Media $media): Response
    {
        $stream      = $this->storage->resolveStream($media, 'file');
        if(empty($stream)){
            throw $this->NotFoundHttpException('No media stream found for file ' . $media->getPathName());
        }
        $response    = new Response(stream_get_contents($stream));
        $response->headers->set('Content-Type', $media->getMimetype());
        return $response;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isDownload(): bool
    {
        return $this->download;
    }

    /**
     * @param bool $download
     */
    public function setDownload(bool $download): void
    {
        $this->download = $download;
    }

    /**
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function pdfContent(Media $media): Response
    {
        $stream      = $this->storage->resolveStream($media, 'file');
        $response = new StreamedResponse(static function () use ($stream): void {
            stream_copy_to_stream($stream, fopen('php://output', 'wb'));
        });

        $disposition = HeaderUtils::makeDisposition(
            $this->isDownload() ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $media->getFileName()
        );
        $response->headers->set('Content-Type', $media->getMimeType());
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Size', $media->getSize());
        return $response;
    }

    /**
     * @param string $mediaClass
     */
    public function setMediaClass(string $mediaClass): void
    {
        $this->mediaClass = $mediaClass;
    }
}
