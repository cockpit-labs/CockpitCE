<?php
/*
 * Core
 * FolderTplDataTransformer.php
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
use App\Entity\Folder\FolderTpl;
use App\Entity\Folder\FolderTplQuestionnaireTpl;
use App\Service\ApplicationGlobals;
use DateTime;

final class FolderTplDataTransformer extends CommonDataProvider implements DataTransformerInterface
{
    /**
     * @var array
     */
    var $position = [];

    /**
     * @param \App\Entity\Folder\FolderTpl $folderTpl
     *
     * @return \App\Entity\Folder\FolderTpl
     * @throws \Exception
     */
    private function get(FolderTpl $folderTpl)
    {
        if (empty($this->getUser()) || $this->getAppClient() === $this->getGlobals()->getStudioClient()) {
            return $folderTpl;
        }

        $toInterval   = '9999-01-01';
        $fromInterval = '1900-01-01';
        if (!empty($this->getRequest()->get('fromdate'))) {
            $fromInterval = $this->getRequest()->get('fromdate');
        }

        if (!empty($this->getRequest()->get('todate'))) {
            $toInterval = $this->getRequest()->get('todate');
        }

        // get min and max dates from calendars
        $expectedFolders = 0;
        $periods         = [];
        foreach ($folderTpl->getCalendars() as $calendar) {

            $folderTpl->setEndDate(max($calendar->getEnd(), $folderTpl->getEndDate()));
            // start cannot be null, so force it to first end value, if it is null
            $folderTpl->setStartDate($folderTpl->getStartDate() ?? $folderTpl->getEndDate());
            $folderTpl->setStartDate(min($calendar->getStart(), $folderTpl->getStartDate()));

            $folderTpl->setPeriodEnd(max($calendar->getPeriodEnd(), $folderTpl->getPeriodEnd()));
            // periodStart cannot be null, so force it to first end value, if it is null
            $folderTpl->setPeriodStart($folderTpl->getPeriodStart() ?? $folderTpl->getPeriodEnd());
            $folderTpl->setPeriodStart(min($calendar->getPeriodStart(), $folderTpl->getPeriodStart()));
            if ($this->context[$this->context['operation_type'] . '_operation_name'] === 'getexpectation' || $this->context[$this->context['operation_type'] . '_operation_name'] === 'periods') {
                $startInterval = new DateTime($fromInterval);
                $startInterval = $startInterval > $calendar->getStart() ? $startInterval : $calendar->getStart();
                $endInterval   = new DateTime($toInterval);
                $endInterval   = $endInterval < $calendar->getEnd() ? $endInterval : $calendar->getEnd();
                $calendar->setStart($startInterval);
                $calendar->setEnd($endInterval);
                $expectedFolders += $calendar->getPeriodCount();
                $periods         = $calendar->getPeriods();
            }
        }

        // calculate periods
        $folderTpl->setExpectedFolders($expectedFolders * $folderTpl->getMinFolders());
        foreach ($periods as $period) {
            $folderTpl->addPeriod($period);
        }

        return $folderTpl;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl $folderTpl
     *
     * @return \App\Entity\Folder\FolderTpl
     */
    private function update(FolderTpl $folderTpl): FolderTpl
    {
        if (empty($folderTpl->questionnaireTpls)) {
            return $folderTpl;
        }

        // force state for now. Always validated
        $folderTpl->setState('VALIDATED');

        // remove all folderTplsQuestionnaireTpls
        if (!empty($folderTpl->getId())) {
            $em                          = $this->getEntityManager();
            $folderTplsQuestionnaireTpls = $em->getRepository(FolderTplQuestionnaireTpl::class)->findBy(['folderTpl' => $folderTpl->getId()]);
            foreach ($folderTplsQuestionnaireTpls as $folderTplQuestionnaireTpl) {
                $em->remove($folderTplQuestionnaireTpl);
            }
            $em->flush();

        }
        // add and sort folderTplsQuestionnaireTpls from questionnaireTpls
        foreach ($folderTpl->questionnaireTpls as $questionnaireTpl) {
            $folderTplQuestionnaireTpl = new FolderTplQuestionnaireTpl();
            $folderTplQuestionnaireTpl->setFolderTpl($folderTpl);
            $folderTplQuestionnaireTpl->setQuestionnaireTpl($questionnaireTpl);
            $folderTplQuestionnaireTpl->setPosition($this->position[$questionnaireTpl->getId()]);
            $folderTpl->addFolderTplsQuestionnaireTpls($folderTplQuestionnaireTpl);
        }
        $folderTpl->removeQuestionnaireTpl();

        return $folderTpl;
    }

    /**
     * @param array $folderTpl
     */
    private function saveQuestionnairePosition(array $folderTpl)
    {

        if (empty($folderTpl['questionnaireTpls'])) {
            return;
        }

        $pos = 1;
        foreach ($folderTpl['questionnaireTpls'] as $questionnaireTpl) {
            if (isset($questionnaireTpl['id'])) {
                // it's an object
                $id = $questionnaireTpl['id'];
            } else {
                // it's an IRI
                $id = ApplicationGlobals::getIdFromIri($questionnaireTpl);
            }
            $this->position[$id] = $pos;
            $pos++;
        }
    }

    /**
     * @param object $data
     * @param string $to
     * @param array  $context
     *
     * @return \App\Entity\Folder\FolderTpl|\App\Entity\Folder\FolderTpl|object
     * @throws \Exception
     */
    public function transform($data, string $to, array $context = [])
    {
        $this->context = $context;
        switch ($context[$context['operation_type'] . '_operation_name']) {
            case 'all':
            case 'get':
            case 'periods':
            case 'getexpectation':
                $folderTpl = $this->get($data);
                break;
            case 'post':
            case 'patch':
                $folderTpl = $this->update($data);
                break;
            default:
                $folderTpl = $data;
                break;
        }

        return $folderTpl;
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
        if (FolderTpl::class !== $to) {
            // just transform FolderTpl
            return false;
        }
        if ($data instanceof FolderTpl
            && ($context['output']['class'] ?? null) !== null
            && in_array($context[$context['operation_type'] . '_operation_name'],
                        ['all', 'get', 'periods', 'getexpectation'])) {
            // it's just a get (item or collection)
            return true;
        }
        if (is_array($data)
            && ($context['input']['class'] ?? null) !== null
            && in_array($context[$context['operation_type'] . '_operation_name'], ['post', 'patch'])) {
            // it's input data
            // as it's json, we can change or store things
            // if it's an object, its already transformed
            $this->saveQuestionnairePosition($data);
            return true;
        }
        return false;

    }
}
