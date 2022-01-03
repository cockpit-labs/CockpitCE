<?php
/*
 * Core
 * ExportTemplateCommand.php
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

namespace App\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTemplateCommand extends CommonCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'cockpit:template:export';
    /**
     * @var bool|null
     */
    private bool|null $sample;

    /**
     * @param $templateFile
     *
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */


    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Export templates');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command to export templates from DB...');

        // configure arguments
        $this->addOption('sample', null, InputOption::VALUE_NONE, 'no pdf generation).');
        $this->addArgument('template', InputArgument::REQUIRED, 'The json data file to export.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        parent::execute($input, $output);
        $this->output = $output;
        $this->input  = $input;
        $templates    = $input->getArgument('template');
        $this->sample       = $input->getOption('sample');

        if (!empty($templates)) {

            if ($this->sample) {
                $entities = [
                    'Block\BlockTpl',
                    'Questionnaire\QuestionnaireTpl',
                ];
            } else {
                $entities = [
                    'Calendar',
                    'Block\BlockTpl',
                    'Questionnaire\QuestionnaireTpl',
                    'Folder\FolderTpl',
                ];
            }
            $excludeAttributes = [
                'all' => [
                    'locale',
                    'resource',
                    'questionTpl',
                    'periodStart',
                    'periodEnd',
                    'folderTpls',
                    'questionTpl',
                    'folders',
                    'targets',
                    'uuid',
                    'startDate',
                    'endDate',
                    'expectedFolders',
                    'parent'
                ]
            ];
            $submitted         = ['state' => 'SUBMITTED'];
            $staticAttributes  = [
                'Questionnaire\QuestionnaireTpl' => $submitted,
                'Folder\FolderTpl'               => $submitted
            ];
            $this->getAPICall()->setUsername($this->adminUser)
                 ->setPassword($this->adminPwd)
                 ->setAdminClient();

            $data = $this->exportData($entities, $excludeAttributes, $staticAttributes);
            $data = json_decode($data, true);

            // replace roles and rights (and sample if needed)
            array_walk_recursive($data, [$this, 'fixRolesRightsAndSamples']);

            $data = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($templates, $data);
        }

        return 0;
    }

    public function fixRolesRightsAndSamples(&$value, $key)
    {
        $roles = array_flip($this->getRoles());

        // fix samples
        $value = ($key === 'sample' && $this->sample) ? true : $value;

        // fix rights
        $value = ($key === 'right') ? basename($value) : $value;

        // fix roles
        $value = in_array($key, ['targetRole', 'userRole']) ? 'role:'.$roles[basename($value)]: $value;
    }
}
