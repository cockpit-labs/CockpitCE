<?php
/*
 * Core
 * CalendarCheckCommand.php
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
use DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarCheckCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:calendar:check';

    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Check and activate/deactivate calendar.');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command calculates calendar availability...');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        parent::execute($input, $output);
        $this->output = $output;
        $this->input  = $input;

        $now = new DateTime();

        $this->getAPICall()->setUsername($this->adminUser)
             ->setPassword($this->adminPwd)
             ->setAdminClient();
        $response  = $this->getAPICall()->doGetRequest(Calendar::class);
        $calendars = json_decode($response->getContent(), true);
        foreach ($calendars as $calendar) {
            $periodStart = new DateTime($calendar['periodStart']);
            $periodend   = new DateTime($calendar['periodEnd']);
            $valid       = ($now >= $periodStart) && ($now <= $periodend);
            if ($calendar['valid'] != $valid) {
                $calendar['valid'] = $valid;
                $this->getAPICall()->doPatchWithActionRequest(Calendar::class, $calendar['id'], $calendar, 'validate');
                $statusCode = $response->getStatusCode();
                if ($statusCode != 200) {
                    $response->getContent();
                    $error = json_decode($response->getContent(), JSON_PRETTY_PRINT);

                    $this->output->write("<error>$error</error>\n");

                }
            }
        }

        return 0;
    }
}
