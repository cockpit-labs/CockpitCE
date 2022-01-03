<?php
/*
 * Core
 * SwaggerDecorator.php
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

namespace App\Swagger;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Folder\FolderTplQuestionnaireTpl;
use App\Entity\Questionnaire\QuestionnaireTplBlockTpl;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    private $decorated;
    /**
     * @var \ApiPlatform\Core\Api\IriConverterInterface
     */
    private $iriService;

    /**
     * @var string[]
     */
    private $hiddenRoutes = [
        FolderTplQuestionnaireTpl::class,
        QuestionnaireTplBlockTpl::class,
    ];

    public function __construct(NormalizerInterface $decorated, IriConverterInterface $iriService)
    {
        $this->decorated  = $decorated;
        $this->iriService = $iriService;
    }

    public function normalize($object, $format = '', array $context = [])
    {
        $entitiesToRemove=[];
        foreach ($this->hiddenRoutes as $hiddenRoute){
            $route=$this->iriService->getIriFromResourceClass($hiddenRoute);
            if(!empty($context['base_url'])) {
                if (substr($route, 0, strlen($context['base_url'])) == $context['base_url']) {
                    $route = substr($route, strlen($context['base_url']));
                }
            }
            $entitiesToRemove[]=$route;
        }

        $docs = $this->decorated->normalize($object, $format, $context);
        $paths=(array)$docs['paths'];

        foreach ($entitiesToRemove as $entity){
            $paths=array_filter($paths, function ($path) use ($entity){
                return substr($path, 0, strlen($entity))!==$entity;
            }, ARRAY_FILTER_USE_KEY);
        }
        $docs['paths']=$paths;
        return $docs;
    }

    public function supportsNormalization(
        $data,
        $format = ''
    ) {
        return $this->decorated->supportsNormalization($data, $format);
    }

}
