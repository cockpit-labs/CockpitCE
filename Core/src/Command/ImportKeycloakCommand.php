<?php
/*
 * Core
 * ImportKeycloakCommand.php
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
use Symfony\Component\Console\Output\OutputInterface;

class ImportKeycloakCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:keycloak:import';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Load keycloak data');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command allows to load the keycloak data in db...');

        // configure an argument
        $this->addArgument('keycloakjson', InputArgument::REQUIRED, 'The json data file to load.');
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

        $keycloakFile = $input->getArgument('keycloakjson');

        if (empty($keycloakFile)
            || !file_exists($keycloakFile)
            || !$this->isJson(file_get_contents($keycloakFile))) {
            $this->output->write("<error>file does not exists or is not a valid json file or is empty...</error>\n");

            return 1;
        }
        $this->getAPICall()->setUsername($this->adminUser)
            ->setPassword($this->adminPwd)
            ->setAdminClient();

        $this->importData($keycloakFile);
        $this->resetPasswordToUsername();
        $this->purgeCaches();

        return 0;
    }

}
