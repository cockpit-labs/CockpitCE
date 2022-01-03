<?php
/*
 * Core
 * InitCoreCommand.php
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

use App\CentralAdmin\KeycloakConnector;
use App\Entity\Config;
use App\Entity\Right;
use App\Entity\User;
use Keycloak\Admin\KeycloakClient;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCoreCommand extends CommonCommand
{
    protected static $defaultName = 'cockpit:core:init';

    /**
     * @var array
     */
    private array $keycloakRealmSetting;

    private function loadKeycloak($keycloakFile)
    {
        $this->output->write("<info>Init Keycloak...</info>\n");
        // creates a  progress bar (50 units)
        $progressBar         = new ProgressBar($this->output, 3);
        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl(),
                                                           'Accept'    => 'application/json, text/plain'
                                                       ]);

        if (!file_exists($keycloakFile)) {
            $this->output->write("<error>file $keycloakFile does not exists</error>\n");
            return -1;
        }

        $keycloakJSON = file_get_contents($keycloakFile);
        $vars=$_ENV;
        $vars['cockpitbaseurl']=$this->getGlobals()->getBaseUrl();
        $vars['cockpitrealm']=$this->getGlobals()->getKcRealm();
        $vars['cockpitcoresecret']=$this->getGlobals()->getKcSecret();
        $keycloakJSON = preg_replace_callback("#%%([a-zA-Z0-9_-]*)%%#",
            function ($variable) use ($vars) {
                return $vars[$variable[1]] ?? $variable[1];
            },                                $keycloakJSON);
        $this->setKeycloakRealmSetting(json_decode($keycloakJSON, true));

        $this->output->write("\n\t<info>deleting realm " . $this->getGlobals()->getKcRealm() . "</info>\n");
        $keycloakAdminClient->deleteRealm(['realm' => $this->getGlobals()->getKcRealm()]);
        $progressBar->advance();

        $this->output->write("\n\t<info>creating realm " . $this->getGlobals()->getKcRealm() . "</info>\n");

        $ret = $keycloakAdminClient->importRealm($this->getKeycloakRealmSetting());

        if (!empty($ret['error'])) {
            $this->output->write("<error>" . $ret['error'] . "</error>\n");
            exit(1);
        }
        $progressBar->advance();
        $this->output->write("\n\t<info>importing $keycloakFile</info>\n");

        $adminKc = new KeycloakConnector(
            $this->getGlobals()->getKcUrl(),
            ['username' => $this->kc_admin, 'password' => $this->kc_adminpwd],
            'admin-cli',
            'master'
        );

        $users = $keycloakAdminClient->getUsers(['realm' => $this->getGlobals()->getKcRealm()]);
        $this->output->write("\n\t<info>setting superuser password</info>\n");
        foreach ($users as $currentUser) {
            $adminKc->setUserPassword($currentUser['id'], $this->getGlobals()->getKcRealm(), $this->adminPwd);
            $this->output->write("<comment>\t\t" . $currentUser['username'] . "</comment>\n");
        }

        $progressBar->advance();
        $progressBar->finish();
        $this->output->write("\n");
    }

    /**
     * @throws \Exception
     */
    private function rebuildDB()
    {
        $this->output->write("<info>Rebuilding DB...</info>");

        // check src owner and permissions

        if ($this->input->getOption('drop')) {
            // drop database
            $this->output->writeln("<comment>Drop DB...</comment>");
            $command   = $this->getApplication()->find('doctrine:database:drop');
            $arguments = ['--force' => true, '--if-exists' => true];
            $command->run(new ArrayInput($arguments), $this->output);
        }
        // create database
        $this->output->writeln("<comment>Create DB...</comment>");
        $command   = $this->getApplication()->find('doctrine:database:create');
        $arguments = [];
        $command->run(new ArrayInput($arguments), $this->output);

        // create database schema
        $this->output->writeln("<comment>Create Schema...</comment>");
        $command   = $this->getApplication()->find('doctrine:schema:update');
        $arguments = ['--force' => true];
        $command->run(new ArrayInput($arguments), $this->output);

        // Set version to latest
        $this->output->writeln("<comment>Set version...</comment>");
        $command   = $this->getApplication()->find('doctrine:migrations:version');
        $arguments = ['--add' => true, '--all' => true];
        $input     = new ArrayInput($arguments);
        $input->setInteractive(false);
        $command->run($input, $this->output);

        $this->output->writeln("<info>Done!\n</info>");
    }

    /**
     *
     */
    private function rebuildKCKeys()
    {
        $this->output->write("<info>\nRebuilding keycloak keys...</info>");

        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl()
                                                       ]);
        $keys                = $keycloakAdminClient->getRealmKeys(['realm' => $this->getGlobals()->getKcRealm()]);
        $key                 = "";
        $publickey           = "";
        foreach ($keys['keys'] as $k) {
            $publickey = $k['publicKey'] ?? $publickey;
            $key       = $k['certificate'] ?? $key;
        }

        // Save publicKey
        $pkey = "-----BEGIN PUBLIC KEY-----\n";
        while ($line = substr($publickey, 0, 64)) {
            $pkey      .= $line . "\n";
            $publickey = substr($publickey, 64);
        }
        $pkey .= "-----END PUBLIC KEY-----";
        $this->getGlobals()->setEntityManager($this->getEntityManager())->setPublicKey($pkey);

        // Save privateKey
        $pkey = "-----BEGIN RSA PRIVATE KEY-----\n";
        while ($line = substr($key, 0, 64)) {
            $pkey .= $line . "\n";
            $key  = substr($key, 64);
        }
        $pkey .= "-----END RSA PRIVATE KEY-----";
        $this->getGlobals()->setEntityManager($this->getEntityManager())->setPrivateKey($pkey);

        $clients  = $keycloakAdminClient->getClients(['realm' => $this->getGlobals()->getKcRealm()]);
        $clientId = '';
        foreach ($clients as $client) {
            $clientId = $client['clientId'] === 'cockpitcore' ? $client['id'] : $clientId;
        }
        $secret = $keycloakAdminClient->getClientSecret([
                                                            'realm' => $this->getGlobals()->getKcRealm(),
                                                            'id'    => $clientId
                                                        ]);
        $secret = $secret['value'];

        if ($secret != $this->getGlobals()->getKcSecret()) {
            $this->output->write("<error>secret in keycloak does not match secret in .env!\n</error>");
        }
        $this->output->write("<info>DONE!\n</info>");
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();
        // the short description shown while running "php bin/console list"
        $this->setDescription('Load templates');

        // the full command description shown when running the command with
        // the "--help" option
        $this->setHelp('This command allows you to load the demo data in db...');
        $this->addOption('testtoken', null, InputOption::VALUE_NONE, 'high delay token.');
        $this->addOption('drop', null, InputOption::VALUE_NONE, 'drop database.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
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
                             'Init core',
                             '=================',
                             '',
                         ]);

        $this->tokenDelayMultiplier = 1;
        if ($input->getOption('testtoken')) {
            $this->tokenDelayMultiplier = 60 * 24;
        }

        $this->cleanStorage();
        $this->rebuildDB();
        $initJson = 'init/keycloak.json';
        if (!file_exists($initJson)) {
            $this->output->writeln("<error>file $initJson does not exists</error>");
            return -1;
        }
        $this->loadKeycloak($initJson);
        $this->rebuildKCKeys();

        $this->output->writeln("<info>Sync roles...</info>");
        // init keycloak client
        $roles = $this->syncKeycloakRoleToCockpit();

        $this->output->writeln("<info>Add Cockpit config...</info>");

        $defaultParams = file_get_contents('init/clients.json');
        $defaultParams = json_decode($defaultParams, true);


        // get global rights from init file
        $globalRights = file_get_contents('init/rights.json');
        $globalRights = json_decode($globalRights, true);

        $this->output->writeln("<info>Add rights...</info>");
        foreach ($globalRights as $rightName=>$description){
            $right = new Right();
            $right->setId($rightName)->setDescription($description);
            $this->getEntityManager()->persist($right);
            $this->getEntityManager()->flush();
        }

        $this->output->writeln("<info>Configure Cockpit clients...</info>");
        $this->getAPICall()->setCoreCall(false);
        $this->getAPICall()->setUsername($this->adminUser)
             ->setPassword($this->adminPwd)
             ->setAdminClient();
        foreach ($defaultParams as $client => $params) {
            $this->output->write("\t<comment>configuring $client</comment>\n");
            $params   = $defaultParams[$client] ?? [];
            $keycloak = [];
            if ($this->getGlobals()->getKcClient($client) != 'none') {
                $keycloak = [
                        'url'      => $this->getGlobals()->getKcAuthUrl(),
                        'realm'    => $this->getGlobals()->getKcRealm(),
                        'clientId' => $this->getGlobals()->getKcClient($client)
                ];
            }
            $config = [
                'client'   => $client,
                'keycloak' => $keycloak,
                'params'   => $params
            ];
            $this->getAPICall()->doPostRequest(Config::class, $config);
        }

        // add superuser
        $this->output->writeln("<info>Add superuser...</info>");
        $data      = json_decode(file_get_contents($initJson), true);
        $users     = $data['users'];
        $superuser = [];
        foreach ($users as $user) {

            if ($user['username'] === 'superuser') {
                $superuser = $user;
            }
        }
        foreach ($superuser['realmRoles'] as $realmRole) {
            $superuser['roles'][] = $roles[$realmRole];
        }
        $superuser['firstname'] = $superuser['firstName'];
        $superuser['lastname']  = $superuser['lastName'];

        $this->getAPICall()->doPostRequest(User::class, $superuser);

        // Init default
        $this->getAPICall()->setUsername($this->adminUser)
             ->setPassword($this->adminPwd)
             ->setAdminClient();
        $this->importData('init/defaults.json');

        return 0;
    }

    /**
     * @return array
     */
    public function getKeycloakRealmSetting(): array
    {
        return $this->keycloakRealmSetting;
    }

    /**
     * @param array $keycloakRealmSetting
     *
     * @return InitCoreCommand
     */
    public function setKeycloakRealmSetting(array $keycloakRealmSetting): InitCoreCommand
    {
        $this->keycloakRealmSetting = $keycloakRealmSetting;
        return $this;
    }
}

