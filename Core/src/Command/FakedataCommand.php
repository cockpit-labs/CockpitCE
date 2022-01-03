<?php
/*
 * Core
 * FakedataCommand.php
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

use App\Entity\Folder\Folder;
use App\Entity\Folder\FolderTpl;
use App\Entity\Media\UserMedia;
use App\Entity\Target;
use App\Entity\User;
use DateTime;
use Faker\Factory;
use Faker\Generator;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FakedataCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:fakedata';
    /**
     * @var bool
     */
    protected bool $simpleimage = false;
    /**
     * @var string
     */
    protected ?string $localimage = '';
    /**
     * @var bool
     */
    protected bool $noimage = false;
    /**
     * @var string
     */
    private ?string $text;
    /**
     * @var Generator
     */
    private Generator $faker;
    /**
     * @var string
     */
    private string $currentFolder;
    /**
     * @var string
     */
    private string $currentQuestionnaire;
    /**
     * @var string
     */
    private string $currentBlock;
    /**
     * @var string
     */
    private string $nopdf = '';
    /**
     * @var bool|string|string[]|null
     */
    private ?string $pdf4 = '';

    /**
     * @param array  $answers
     * @param string $rawValue
     * @param string $choice
     */
    private function addAnswer(array &$answers, string $rawValue, $choice)
    {
        if (!empty($choice) && !empty($choice->id)) {
            $choice = $choice->id;
        } else {
            $choice = null;
        }
        $answers[] = ['rawValue' => $rawValue ?? "", "choice" => $choice ?? null];
    }

    /**
     * @param string $filename
     * @param string $ext
     *
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function addPhotos(string $filename)
    {
        $tmpFile = $this->kernel->getLocalTmpDir() . '/' . basename($filename);
        copy($filename, $tmpFile);

        $response = $this->getAPICall()->doUploadFileRequest(UserMedia::class, $tmpFile);
        $this->checkResponse($response, [201]);
        $response = json_decode($response->getContent(), true);
        unlink($tmpFile);

        return $response['id'];
    }

    /**
     * @param \Symfony\Contracts\HttpClient\ResponseInterface $response
     * @param array|int[]                                     $validCode
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function checkResponse(ResponseInterface $response, array $validCode = [200]): void
    {
        $statusCode = $response->getStatusCode();
        if (!in_array($statusCode, $validCode)) {
            $content = $response->getContent();
            $this->output->write("<error>error code $statusCode</error>\n");
            $error = json_decode($content, JSON_PRETTY_PRINT);

            $this->output->write("<error>$error</error>\n");
            exit(1);
        }
    }

    /**
     * @param \stdClass $folderTpl
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function fillFolder(stdClass &$folderTpl)
    {
        $this->currentFolder = $folderTpl->label;
        $response            = $this->getAPICall()->doGetRequest(User::class);
        $user                = json_decode($response->getContent());
        foreach ($folderTpl->questionnaires as &$questionnaire) {
            $this->currentQuestionnaire = $questionnaire->label;

            foreach ($questionnaire->blocks as $block) {
                $this->currentBlock = $block->label;
                $bWeb               = empty($this->localimage) && !$this->simpleimage;
                $photoFilenames     = $this->getImageFiles($bWeb, $this->simpleimage, 1000);
                foreach ($block->questions as &$question) {
                    $answers = [];
                    $choices = $question->choices;
                    if (empty($choices)) {
                        continue;
                    }

                    $writeRender          = $question->writeRenderer;
                    $maxChoices           = $question->maxChoices > 0 ?: count($choices);
                    $maxPhotos            = $question->maxPhotos;
                    $question->minChoices = $question->mandatory ? 1 : $question->minChoices;
                    $nbChoices            = rand($question->minChoices, $maxChoices);
                    $nbPhotos             = $maxPhotos;
                    $nbPhotos             = rand(0, ($this->noimage ? 0 : $nbPhotos));
                    $photos               = [];

                    while ($nbPhotos) {
                        if (!empty($photoFilenames)) {
                            $p = array_shift($photoFilenames);
//                            $this->output->write("\t\t        <info> add photos $p\n</info>");
                            $photos[] = $this->addPhotos($p);
                        }
                        $nbPhotos--;
                    }

                    $question->photos = $photos;
                    if ($nbChoices === 0) {
                        continue;
                    }
                    $choicesIndex = array_rand(json_decode(json_encode($choices), true), $nbChoices);
                    if (is_array($choicesIndex)) {
                        foreach ($choicesIndex as $chosenChoice) {
                            $chosenChoices[] = $choices[$chosenChoice];
                        }
                    } else {
                        $chosenChoices = [$choices[$choicesIndex]];
                    }

                    foreach ($chosenChoices as $chosenChoice) {
                        switch ($writeRender->component) {
                            default:
                            case 'none':
                                break;
                            case 'text':
                                $this->addAnswer($answers, $this->getSentences('Answer', rand(2, 20), 1)[0],
                                                 $chosenChoice);
                                break;
                            case 'select':
                                $this->addAnswer($answers, strval($chosenChoice->position), $chosenChoice);
                                break;
                            case 'dateTime':
                                $this->addAnswer($answers,
                                                 $this->faker->dateTimeBetween('-1 years',
                                                                               $this->getAPICall()->getFakedate())->format("c"),
                                                 $chosenChoice);
                                break;
                            case
                            'range': // ??
                            case 'number':
                                $min = $writeRender->min ?? 0;
                                $max = $writeRender->max ?? 0;
                                $this->addAnswer($answers, strval($this->faker->numberBetween($min, $max)),
                                                 $chosenChoice);
                                break;
                        }
                    }
                    $question->answers = $answers;
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getCurrentEntityPath(): string
    {
        return $this->currentFolder . '/' . $this->currentQuestionnaire . '/' . $this->currentBlock;
    }

    /**
     * @param bool $bWeb
     * @param bool $grumpy
     * @param int  $count
     *
     * @return array
     */
    private function getImageFiles(bool $bWeb = true, bool $grumpy = false, int $count = 1): array
    {
        $filenames = [];
        if ($bWeb) {
            while ($count--) {
                $filenames[] = "https://picsum.photos/500/" . rand(250, 350);
            }
        } elseif ($grumpy) {
            $filenames = array_fill(0, $count, __DIR__ . '/GrumpyBear.jpg');
        } elseif (!empty($this->localimage)) {
            if (is_dir($this->localimage)) {
                $imageDir = $this->localimage;
            } elseif (is_file($this->localimage)) {
                $dispatching = $this->localimage;
                $imageDir    = '/dev/null';
                $dispatching = file_get_contents($dispatching);
                $dispatching = json_decode($dispatching, true);
                $path        = $this->getCurrentEntityPath();
                if (!empty($dispatching[$path])) {
                    if ($this->localimage[0] != '/') {
                        $path = $this->kernel->getProjectDir() . '/' . dirname($this->localimage) . '/' . $dispatching[$path];
                    } else {
                        $path = dirname($this->localimage) . '/' . $dispatching[$path];
                    }
                    $imageDir = is_dir($path) ? $path : '';
                }
            }
            $filenames = glob($imageDir . '/*.jpg');
            shuffle($filenames);
        }
        return array_slice($filenames, 0, min(count($filenames), $count));
    }

    /**
     * @param null $type
     * @param int  $words
     * @param int  $count
     *
     * @return array
     */
    private function getSentences($type = null, $words = 20, int $count = 1): array
    {
        $sentences = [];
        if (!empty($this->text) && file_exists($this->text)) {
            $allSentences = file_get_contents($this->text);
            $allSentences = json_decode($allSentences, true);
            if (!empty($allSentences[$type])) {

                $sentences = $allSentences[$type];
                shuffle($sentences);
            }
        }
        if (empty($sentences)) {
            $c = $count;
            while ($c) {
                $sentences[] = $this->faker->sentence($words);
                $c--;
            }
        }
        return array_slice($sentences, 0, min($count, count($sentences)));
    }

    /**
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function makeFakeData(array $users): int
    {
        // process each user
        if (true) {
            foreach ($users as $user) {
                if ($user === 'superuser') {
                    continue;
                }
                $this->getAPICall()->setUsername($user)
                     ->setPassword($user)
                     ->setViewClient();
                $this->output->write("<info>Processing user $user</info>\n");
                // get targets
                $response = $this->getAPICall()->doGetRequest(Target::class, null, null, ['right' => 'CREATE']);
                $this->checkResponse($response);

                $targets = json_decode($response->getContent(), true);

                // process each target
                foreach ($targets as $target) {
                    // get FolderTpls
                    $folderTpl = $target['folderTpl'];

                    // process each Template Folder
                    $response = $this->getAPICall()->doGetRequest(FolderTpl::class, $folderTpl['id'], 'periods');
                    $this->checkResponse($response);
                    $folderTpl = json_decode($response->getContent(), true);
                    $periods   = $folderTpl['periods'];
                    foreach ($periods as $idx => $period) {
                        if (strtotime($period['start']) >= time()) {
                            unset($periods[$idx]);
                        }
                    }
                    $folderLabel = $folderTpl['label'];
                    $this->output->write("<info>\t\tProcessing Folder $folderLabel on group " . $target['groupLabel'] . " </info>\n");
                    $this->output->write("<info>\t\t    create folders for " . count($periods) . " periods</info>\n");
                    $this->getAPICall()->setUsername($user)
                         ->setPassword($user)
                         ->setViewClient();

                    // process each period
                    foreach ($periods as $period) {
                        // maybe, create the folder
                        // make some tricks to randomize creation (or not)
                        $minFolders   = $folderTpl['minFolders'] ?? 1;
                        $maxFolders   = $folderTpl['maxFolders'] ?? 3; // 0 means no limit...
                        $maxFolder    = rand($minFolders, $maxFolders); // decrease maxfolder randomly
                        $minFolder    = rand($minFolders, $maxFolders);
                        $startPeriod  = new DateTime($period['start']);
                        $endPeriod    = new DateTime($period['end']);
                        $now          = time();
                        $actualFolder = $now > $startPeriod->getTimestamp() && $now < $endPeriod->getTimestamp();
                        for ($occurence = $minFolder; $occurence <= $maxFolder; $occurence++) {
                            // set the date and time
                            $date     = $this->faker->dateTimeBetween($period['start'], $period['end']);
                            $hourDiff = random_int(-11, 11);
                            $minDiff  = random_int(-59, 59);
                            $date->modify("NOON $hourDiff HOURS $minDiff MINUTES");
                            $this->output->write("<info>\t\t    create at " . $date->format('r') . " </info>\n");
                            $this->getAPICall()->setFakeDate($date->format('r'));

                            // create a folder
                            $folder   = [
                                'appliedTo' => $target['group'],
                                'folderTpl' => $folderTpl['id']
                            ];
                            $response = $this->getAPICall()->doPostRequest(Folder::class, $folder, "create");
                            $this->checkResponse($response, [201]);

                            $folder = json_decode($response->getContent());
                            $this->fillFolder($folder);
                            $folder = json_decode(json_encode($folder), true);

                            $response = $this->getAPICall()->doPatchRequest(Folder::class, $folder['id'], $folder);
                            $this->checkResponse($response);
                            // no transition if now is inside a period
                            if (!$actualFolder) {
                                $this->getAPICall()->setFakeDate($folder['createdAt']);

                                $response = $this->getAPICall()->doPatchWithActionRequest(Folder::class,
                                                                                          $folder['id'],
                                                                                          [],
                                                                                          'validate');
                                $this->checkResponse($response, [200, 403]);
                                if ($response->getStatusCode() == 403) {
                                    break;
                                }
                                $folder = json_decode($response->getContent(), true);
                            }
                            $folder = null;
                        }
                    }
                    $this->output->write("\n");
                }
            }
        }

        $this->output->write("\n");
        return 0;
    }

    /**
     *
     */
    protected function configure(): void
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Create fake data in DB');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command allows you to create fake data in db...');
        $this->addArgument('users', InputArgument::IS_ARRAY | InputArgument::OPTIONAL);
        $this->addOption('simpleimage', null, InputOption::VALUE_NONE, 'no picsum images.');
        $this->addOption('localimage', null, InputOption::VALUE_REQUIRED, 'get local images.');
        $this->addOption('text', null, InputOption::VALUE_REQUIRED, 'get local text (json file).');
        $this->addOption('noimage', null, InputOption::VALUE_NONE, 'no images at all).');
        $this->addOption('nopdf', null, InputOption::VALUE_NONE, 'no pdf generation).');
        $this->addOption('pdf4', null, InputOption::VALUE_REQUIRED,
                         'force pdf creation for this user if nopdf is set.');

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
        $output->writeln([
                             'Fake data creating',
                             '==================',
                             '',
                         ]);
        $this->simpleimage = $input->getOption('simpleimage');
        $this->localimage  = $input->getOption('localimage');
        $this->pdf4        = $input->getOption('pdf4');
        $this->noimage     = $input->getOption('noimage');
        $this->text        = $input->getOption('text');
        $this->nopdf       = $input->getOption('nopdf') ? "&nopdf=1" : "";
        $this->faker       = Factory::create();

        if (!empty($input->getArgument('users'))) {
            $users = $input->getArgument('users');
        } else {
            $this->getAPICall()->setUsername($this->adminUser)
                 ->setPassword($this->adminPwd)
                 ->setAdminClient();
            $response = $this->getAPICall()->doGetRequest(User::class);
            $users    = json_decode($response->getContent(), true);
            $users    = array_map(function ($v) {
                return $v['username'];
            }, $users);
        }
        $this->makeFakeData($users);
        $this->purgeCaches();

        return 0;
    }

}
