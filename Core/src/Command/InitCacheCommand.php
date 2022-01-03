<?php
/*
 * Core
 * InitCacheCommand.php
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

use App\Entity\Calendar;
use App\Entity\User;
use DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCacheCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:cache:init';

    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Init cache.');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command init the cache like a first call to app...');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        parent::execute($input, $output);
        $this->output = $output;
        $this->input  = $input;

        $this->getAPICall()->setUsername($this->adminUser)
             ->setPassword($this->adminPwd)
             ->setAdminClient();
        $response            = $this->getAPICall()->doGetRequest(User::class);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            $this->output->write("<error>error code $statusCode</error>\n");
            return 1;
        }

        $this->output->write("<info>Cache initialized</info>\n");
        return 0;
    }
}
