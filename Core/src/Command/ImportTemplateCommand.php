<?php
/*
 * Core
 * ImportTemplateCommand.php
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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTemplateCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:template:import';

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    protected function cleanTables()
    {
        $connection = $this->entityManager->getConnection();
        $driver     = $connection->getDriver();
        $isMysql    = ($driver->getName() == "pdo_mysql");
        $tables     = $connection->getSchemaManager()->listTableNames();

        $this->output->write("<info>Cleaning DB\n</info>\n");
        $progressBar = new ProgressBar($this->output, count($tables));
        $progressBar->start();
        if ($isMysql) {
            $sql  = 'SET FOREIGN_KEY_CHECKS = 0;';
            $stmt = $connection->prepare($sql);
            $stmt->execute();
        }
        foreach ($tables as $table) {
            if (!in_array($table, [
                'KeycloakKey',
                'Config',
                'Attributes',
                'Groups',
                'Users',
                'Roles',
                'Users_Roles',
                'Users_EffectiveRoles',
                'Users_Groups',
                'Users_ChildGroups',
                'Groups_Roles',
                'Rights',
                'migration_versions'
            ])) {
                if ($isMysql) {
                    $sql = "TRUNCATE TABLE `$table`;";
                } else {
                    $sql = "DELETE FROM `$table`;";

                }
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            }
            $progressBar->advance();
        }
        if ($isMysql) {
            $sql  = 'SET FOREIGN_KEY_CHECKS = 1;';
            $stmt = $connection->prepare($sql);
            $stmt->execute();
        }
        $progressBar->finish();
        $this->output->write("\n");
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Import templates');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command to import templates in DB...');

        // configure an argument
        $this->addArgument('template', InputArgument::REQUIRED, 'The json data file to import.');

        $this->addOption('clean', null, InputOption::VALUE_NONE, 'clean all data ');
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
        $templates = $input->getArgument('template');
        if (!empty($templates)) {
            if ($input->getOption('clean')) {
                $this->cleanTables();
                $this->cleanStorage();
            }
            $this->getAPICall()->setUsername($this->adminUser)
                 ->setPassword($this->adminPwd)
                 ->setAdminClient();
            $this->importData($templates);
        }
        $this->purgeCaches();

        return 0;
    }

}
