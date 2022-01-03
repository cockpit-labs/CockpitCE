<?php
/*
 * Core
 * LogsClearCommand.php
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


use App\Entity\FolderTpl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class LogsClearCommand extends Command
{
    protected static $defaultName       = 'logs:clear';

    public function __construct(ContainerInterface $container)
    {
        $this->logDir=$container->get('kernel')->getLogDir();
        $this->env=$container->get('kernel')->getEnvironment();
        parent::__construct();
    }
        protected function configure()
    {
        // the short description shown while running "php bin/console list"
        $this->setDescription('Clean logs files.');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command allows you to clean logs files...');

    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $this->output      = $output;
        $this->input       = $input;

        $this->fs = new Filesystem();

        $log = $this->logDir . '/' . $this->env . '.log';
        $output->write(sprintf("<comment>Clearing the logs for the <info>%s</info> environment</comment>\n", $this->env));
        $this->fs->remove($log);
        if (!$this->fs->exists($log)) {
            $output->write(sprintf("<info>Logs for the '%s' environment was successfully cleared.</info>\n", $this->env));
        } else {
            $output->write(sprintf("<error>Logs for the '%s' environment could not be cleared.</error>\n", $this->env));
        }

        return 0;
    }
}
