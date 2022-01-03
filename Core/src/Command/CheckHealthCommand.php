<?php
/*
 * Core
 * CheckHealthCommand.php
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

use App\Entity\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckHealthCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:health:check';


    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Check core');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command check core health...');
        $this->addOption('port', '', InputOption::VALUE_REQUIRED, 'port number ');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $this->output = $output;
        $this->input  = $input;

        $this->healthcheck = true;
        parent::execute($input, $output);

        $this->getAPICall()->setUsername($this->adminUser)
             ->setPassword($this->adminPwd)
             ->setViewClient();
        $response=$this->getAPICall()->doGetRequest(Config::class);
        $statusCode = $response->getStatusCode();
        if($statusCode===200){
            $this->output->write("<info>Healthy</info>\n");
            return 0;
        }
        $this->output->write("<error>Unhealthy</error>\n");
        return 1;
    }
}
