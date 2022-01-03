<?php
/*
 * Core
 * QuestionnaireTplDataTransformer.php
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
use App\Entity\Questionnaire\QuestionnaireTpl;
use App\Entity\Questionnaire\QuestionnaireTplBlockTpl;
use App\Service\ApplicationGlobals;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

final class QuestionnaireTplDataTransformer extends CommonDataProvider implements DataTransformerInterface
{
    /**
     * @var array
     */
    var $position = [];

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTpl $questionnaireTpl
     *
     * @return \App\Entity\Questionnaire\QuestionnaireTpl
     */
    private function update(QuestionnaireTpl $questionnaireTpl)
    {
        $em = $this->getEntityManager();

        if (empty($questionnaireTpl->blockTpls)) {
            return $questionnaireTpl;
        }

        // remove all questionnaireTplBlockTpls
        if (!empty($questionnaireTpl->getId())) {
            $questionnaireTplsBlockTpls = $em->getRepository(QuestionnaireTplBlockTpl::class)->findBy(['questionnaireTpl' => $questionnaireTpl->getId()]);
            foreach ($questionnaireTplsBlockTpls as $questionnaireTplBlockTpl) {
                $em->remove($questionnaireTplBlockTpl);
            }
            $em->flush();
        }

        // add and sort questionnaireTplBlockTpls from questionnaireTpls
        foreach ($questionnaireTpl->blockTpls as &$blockTpl) {
            $questionnaireTplBlockTpl = new QuestionnaireTplBlockTpl();
            $questionnaireTplBlockTpl->setQuestionnaireTpl($questionnaireTpl);
            // extract position from label
            $questionnaireTplBlockTpl->setBlockTpl($blockTpl);
            $id = $blockTpl->getId() ?? md5($blockTpl->getLabel());
            $questionnaireTplBlockTpl->setPosition($this->position[$id]);
            $questionnaireTpl->addQuestionnaireTplBlockTpls($questionnaireTplBlockTpl);
        }
        $questionnaireTpl->removeBlockTpl();

        foreach ($questionnaireTpl->getBlockTpls() as &$blockTpl) {
            foreach ($blockTpl->getQuestionTpls() as $questionTpl) {
                if (empty($questionTpl->getChoiceTpls())) {
                    throw new NotAcceptableHttpException("No choice for question template ".$questionTpl->getId());
                }
            }
        }

        return $questionnaireTpl;

    }

    private function updateInputData(array &$questionnaireTpl)
    {
        if (empty($questionnaireTpl['blockTpls'])) {
            return;
        }

        $pos = 0;
        foreach ($questionnaireTpl['blockTpls'] as $blockTpl) {
            if (is_array($blockTpl)) {
                if (isset($blockTpl['id'])) {
                    // it's an object
                    $id = $blockTpl['id'];
                } else {
                    $id = md5($blockTpl['label']);
                }
            } elseif (is_string($blockTpl)) {
                // it's an IRI
                $id = ApplicationGlobals::getIdFromIri($blockTpl);
            }
            $this->position[$id] = $pos;
            $pos++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transform($questionnaireTpl, string $to, array $context = [])
    {
        if (empty($this->getUser())) {
            return $questionnaireTpl;
        }

        switch ($context[$context['operation_type'] . '_operation_name']) {
            case 'post':
            case 'patch':
            case 'update':
                $questionnaireTpl = $this->update($questionnaireTpl);
                break;

            default:
                break;
        }
        return $questionnaireTpl;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if (QuestionnaireTpl::class !== $to) {
            // just transform QuestionnaireTpl
            return false;
        }
        if (is_array($data)
            && ($context['input']['class'] ?? null) !== null
            && in_array($context[$context['operation_type'] . '_operation_name'], ['post', 'patch', 'update'])) {
            // it's input data
            // as it's json, we can change things
            // if it's an object, its already transformed

            $this->updateInputData($data);
            return true;
        }
        return false;

    }
}
