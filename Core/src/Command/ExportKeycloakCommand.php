<?php
/*
 * Core
 * ExportKeycloakCommand.php
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

class ExportKeycloakCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:keycloak:export';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Export keycloak data');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command allows to export the keycloak data from db...');

        // configure an argument
        $this->addArgument('keycloakjson', InputArgument::REQUIRED, 'The json data file to load.');
        $this->addOption('nativekeycloak', null, InputOption::VALUE_NONE, 'export file in a keycloak native format');
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

        $exportFile = $input->getArgument('keycloakjson');

        if (!empty($exportFile)) {
            if ($input->getOption('nativekeycloak')) {
                $this->exportKeycloak($exportFile, $input->getOption('noids'));
            } else {
                $entities          = [
                    'Role',
                    'Group',
                    'User',
                ];
                $excludeAttributes = [
                    'all'   => [
                        'locale',
                        'resource',
                        'uuid'
                    ],
                    'Role'  => ['users', 'groups', 'system'],
                    'Group' => ['users'],
                    'User'  => ['effectiveRoles']
                ];
                $preserveIds       = ['Role'];
                $this->getAPICall()->setUsername($this->adminUser)
                     ->setPassword($this->adminPwd)
                     ->setAdminClient();
                $data              = $this->exportData($entities, $excludeAttributes, [], $preserveIds);
                file_put_contents($exportFile, $data);
            }
        }

        return 0;
    }

}
