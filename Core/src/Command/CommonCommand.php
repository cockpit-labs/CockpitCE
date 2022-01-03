<?php
/*
 * Core
 * CommonCommand.php
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
use App\Entity\Calendar;
use App\Entity\Role;
use App\Service\APICall;
use App\Service\ApplicationGlobals;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Keycloak\Admin\KeycloakClient;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class CommonCommand extends Command
{
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface|null
     */
    public ?HttpClientInterface $browserClient = null;
    /**
     * @var string
     */
    protected string $kc_admin = "admin";
    /**
     * @var string
     */
    protected string $kc_adminpwd = '';
    /**
     * @var string
     */
    protected string $adminUser = "superuser";
    /**
     * @var string
     */
    protected string $adminPwd = "";
    /**
     * @var int
     */
    protected int $time_start = 0;
    /**
     * @var string|null
     */
    protected ?string $token = '';
    /**
     * @var array
     */
    protected array $options = [];
    /**
     * @var array
     */
    protected array $headers = [];
    /**
     * @var string
     */
    protected string $cockpitClient = 'cockpitview'; // normal user. Not admin
    /**
     * @var string
     */
    protected string $user = "";
    /**
     * @var string
     */
    protected string $password = "";
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected FilesystemInterface $mediaFilesystem;
    /**
     * @var string
     */
    protected string $fakedate = '';
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface|null
     */
    protected ?KernelInterface $kernel = null;
    /**
     * @var \string[][]
     */
    protected array $roleView = [
        "roles" => [
            "CKP_Dashboard",
            "CKP_User",
        ]
    ];
    /**
     * @var \string[][]
     */
    protected array $roleAdmin = [
        "roles" => [
            "CKP_Dashboard",
            "CKP_User",
            "CKP_Admin",
        ]
    ];
    /**
     * @var array
     */
    protected array $roles;
    /**
     * @var string
     */
    protected string $userId;
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected InputInterface $input;
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected OutputInterface $output;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface|null
     */
    protected ?EntityManagerInterface $entityManager = null;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var int
     */
    protected int $tokenDelayMultiplier;
    /**
     * @var string
     */
    protected string $iriBase;
    /**
     * @var array|string[]
     */
    private array $internalDbNames = [
        'information_schema',
        'keycloak',
        'mysql',
        'performance_schema',
        'sys',
        'cockpit'
    ];
    /**
     * @var string
     */
    private string $impersonateUser = '';
    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $params;
    private APICall               $APICall;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface                             $kernel
     * @param \Doctrine\ORM\EntityManagerInterface                                      $entityManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $params
     * @param \League\Flysystem\FilesystemInterface                                     $acmeFilesystem
     * @param \App\Service\ApplicationGlobals                                           $globals
     * @param \Psr\Log\LoggerInterface                                                  $applogger
     */
    public function __construct(
        KernelInterface        $kernel,
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $params,
        FilesystemInterface    $acmeFilesystem,
        LoggerInterface        $applogger,
        APICall                $APICall

    ) {
        $this->APICall = $APICall;
        $this->APICall->setHttpClient($this->getBrowserClient());
        $this->globals = $this->APICall->getGlobals();

        $this->logger          = $applogger;
        $this->entityManager   = $entityManager;
        $this->kernel          = $kernel;
        $this->params          = $params;
        $this->mediaFilesystem = $acmeFilesystem;

        $this->iriBase = 'core/' . dirname($this->getGlobals()->getIriConverter()->getIriFromResourceClass(Calendar::class));

        parent::__construct();
    }

    /**
     * @param array $data
     * @param array $notAllowed
     */
    private function cleanItem(array &$data, array $notAllowed)
    {
        foreach ($data as $key => &$item) {
            if (!is_integer($key) && in_array($key, $notAllowed)) {
                unset($data[$key]);
            } elseif (is_array($item)) {
                $this->cleanItem($item, $notAllowed);
            }
        }
    }

    /**
     * @param array $data
     */
    private function cleanUuid(array &$data)
    {
        foreach ($data as $key => &$item) {
            if ($key === 'id' && Uuid::isValid($item)) {
                unset($data[$key]);
            } elseif (is_array($item)) {
                $this->cleanUuid($item);
            }
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function mb_basename(string $path): string
    {
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } else {
            if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }

    private function replaceItemId(array &$data, string $id, string $reference)
    {
        foreach ($data as $key => &$item) {
            if (is_array($item)) {
                $this->replaceItemId($item, $id, $reference);
            } elseif ($key == 'id' && $item == $id) {
                // replace item
                $data = $reference;
                return;
            } elseif (preg_match("/\/$id\$/", $item)) {
                $item = $reference;
            } elseif (preg_match('|^' . $this->iriBase . '|', $item)) {
                unset($data[$key]);
            }
        }
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function cleanStorage()
    {
        $files = $this->mediaFilesystem->listContents($this->globals->getFqdn() . '/', true);
        foreach ($files as $file) {
            $this->mediaFilesystem->delete($file['path']);
        }
    }

    /**
     *
     */
    protected function configure()
    {
        $this->addOption('adminpwd', null, InputOption::VALUE_REQUIRED, 'The keycloak/superuser password.',
                         $_ENV['KEYCLOAK_PASSWORD'] ?? '');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->adminPwd = $this->kc_adminpwd = $input->getOption('adminpwd');
        return 0;
    }

    /**
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface|null
     */
    protected function getBrowserClient(): ?HttpClientInterface
    {
        if (empty($this->browserClient)) {
            $this->browserClient = HttpClient::create();
        }
        return $this->browserClient;
    }

    /**
     * @param string $msg
     */
    protected function logUseTime(string $msg)
    {
        $useTime          = (hrtime(true) - $this->time_start) / 100000;
        $this->time_start = hrtime(true);
        $msg              = "[execution time $useTime ms] $msg";
        $this->getLogger()->debug($msg);
    }

    /**
     * @param array $entities
     * @param array $excludeAttributes
     * @param array $staticAttributes
     * @param array $preserveIds
     *
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function exportData(
        array $entities = [],
        array $excludeAttributes = [],
        array $staticAttributes = [],
        array $preserveIds = []
    ): string {
        // processing templates
        $data      = [];
        $idMapping = [];
        foreach ($entities as $entity) {

            $response = $this->getAPICall()->doGetRequest('App\\Entity\\' . $entity);
            $i        = 1;
            $items    = [];
            foreach (json_decode($response->getContent(), true) as $item) {
                if (!in_array($entity, $preserveIds)) {
                    $idMapping[$item['id']] = $this->mb_basename($entity) . str_pad($i, 2, "0", STR_PAD_LEFT);
                    $item['id']             = $idMapping[$item['id']];
                }
                $i++;
                $excludeAttr = [];
                $excludeAttr = !empty($excludeAttributes[$entity]) ? array_merge($excludeAttributes[$entity],
                                                                                 $excludeAttr) : $excludeAttr;
                $excludeAttr = !empty($excludeAttributes['all']) ? array_merge($excludeAttributes['all'],
                                                                               $excludeAttr) : $excludeAttr;
                $this->cleanItem($item, $excludeAttr);
                if (!empty($staticAttributes[$entity])) {
                    $item = array_merge($item, $staticAttributes[$entity]);
                }
                $items[] = $item;
            }
            $data[] = [$entity => $items];
        }
        foreach ($idMapping as $id => $localid) {
            $this->replaceItemId($data, $id, "localid:$localid");
        }

        // remove all uuid
        $this->cleanUuid($data);
        $datajson = json_encode($data, JSON_PRETTY_PRINT);

        $this->output->write("\n");
        return $datajson;
    }

    /**
     * @param string $keycloakFile
     * @param bool   $bCleanId
     *
     * @return bool
     */
    public function exportKeycloak(string $keycloakFile, bool $bCleanId = false): bool
    {
        $this->output->write("<info>Processing Keycloak export...</info>\n");

        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl(),
                                                           'Accept'    => 'application/json, text/plain'
                                                       ]);
        $data                = [];

        // get groups
        $data['groups'] = $keycloakAdminClient->getGroups([
                                                              'realm'               => $this->getGlobals()->getKcRealm(),
                                                              'briefRepresentation' => false
                                                          ]);

        // get realm roles
        $data['roles']['realm'] = $keycloakAdminClient->getRealmRoles([
                                                                          'realm'               => $this->getGlobals()->getKcRealm(),
                                                                          'briefRepresentation' => false
                                                                      ]);
        // remove non cockpit roles, and non core roles
        $data['roles']['realm'] = KeycloakConnector::filterCockpitRole($data['roles']['realm']);

        // get users
        $data['users'] = $keycloakAdminClient->getUsers([
                                                            'realm'               => $this->getGlobals()->getKcRealm(),
                                                            'briefRepresentation' => false
                                                        ]);

        // remove superuser
        $data['users'] = array_values(array_filter($data['users'], function ($item) {
            return $item['username'] != 'superuser';
        }));

        // add groups to users
        $data['users'] = array_values(array_map(function ($item) use ($keycloakAdminClient) {
            $item['groups'] = array_map(function ($g) {
                return $g['path'];
            }, $keycloakAdminClient->getUserGroups([
                                                       'realm' => $this->getGlobals()->getKcRealm(),
                                                       'id'    => $item['id']
                                                   ]));

            return $item;
        }, $data['users']));

        // add roles to users
        $data['users'] = array_values(array_map(function ($item) use ($keycloakAdminClient) {
            $item['realmRoles'] = array_map(function ($g) {
                return $g['name'];
            }, $keycloakAdminClient->getUserRealmRoleMappings([
                                                                  'realm' => $this->getGlobals()->getKcRealm(),
                                                                  'id'    => $item['id']
                                                              ]));

            return $item;
        }, $data['users']));

        // remove all empty attributes
        $data = KeycloakConnector::filterNotNull($data);

        // remove all ids
        if ($bCleanId) {
            $data = KeycloakConnector::filterNotId($data);
            $data = KeycloakConnector::filterNotNull($data);
        }

        $data['ifResourceExists'] = 'OVERWRITE';
        file_put_contents($keycloakFile, json_encode($data, JSON_PRETTY_PRINT));
        $this->output->write("\n");
        return true;
    }

    /**
     * @return \App\Service\APICall
     */
    public function getAPICall(): APICall
    {
        return $this->APICall;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return \App\Service\ApplicationGlobals
     */
    public function getGlobals(): ApplicationGlobals
    {
        return $this->globals;
    }

    /**
     * @return String
     */
    public function getImpersonateUser(): string
    {
        return $this->impersonateUser;
    }

    /**
     * @param string $impersonateUser
     *
     * @return $this
     */
    public function setImpersonateUser(string $impersonateUser): self
    {
        $this->impersonateUser = $impersonateUser;
        return $this;
    }

    /**
     * @param string $roleName
     *
     * @return array
     */
    public function getKcRole(string $roleName): array
    {
        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl(),
                                                           'Accept'    => 'application/json, text/plain'
                                                       ]);
        return $keycloakAdminClient->getRealmRole([
                                                      'realm'     => $this->getGlobals()->getKcRealm(),
                                                      'role-name' => $roleName
                                                  ]);
    }

    /**
     * @return array
     */
    public function getKcRoles(): array
    {
        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl(),
                                                           'Accept'    => 'application/json, text/plain'
                                                       ]);
        return $keycloakAdminClient->getRealmRoles(['realm' => $this->getGlobals()->getKcRealm()]);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        if (empty($this->roles)) {
            $result = $this->getEntityManager()->getRepository(Role::class)->findAll();
            foreach ($result as $role) {
                $this->roles[$role->getName()] = $role->getId();
            }
        }
        return $this->roles;
    }

    /**
     * @param string $username
     */
    public function impersonate(string $username = '')
    {
        $this->setImpersonateUser($username);
    }

    /**
     * @param string $importFile
     *
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function importData(string $importFile): int
    {
        if (!empty($importFile)) {
            $entities = file_get_contents($importFile);
            $entities = json_decode($entities, true);
            $this->output->write("Loading data...\n");

            // processing templates
            $this->getAPICall()->setUsername($this->adminUser)
                 ->setPassword($this->adminPwd)
                 ->setAdminClient();
            $this->getAPICall()->setCoreCall(false);
            $idMapping = [];
            foreach ($entities as $entity) {
                $entitytype = key($entity);
                $this->output->write("\tInjecting '$entitytype' \n");
                foreach ($entity as $listitem) {
                    $progressBar = new ProgressBar($this->output, count($listitem));
                    $progressBar->start();
                    foreach ($listitem as $item) {
                        $progressBar->advance();
                        // store local id
                        $currentLocalid = $item['id'] ?? 'noid';
                        unset($item['id']);
                        // process calculated values
                        array_walk($item, function (&$value) {
                            if (empty($value) || is_array($value)) {
                                return;
                            }
                            // calculated date
                            if (preg_match('/date:(.*)/', $value, $matches)) {
                                $date = new DateTime('now');
                                $date->modify($matches[1]);
                                $value = $date->format('r');
                            }
                        });

                        // calculated ids
                        // replace localid if needed
                        $jsonitem = json_encode($item);
                        foreach ($idMapping as $localid => $id) {
                            $jsonitem = str_replace("localid:$localid", $id, $jsonitem);
                        }
                        // replace system roles if needed
                        $jsonitem = preg_replace_callback(
                            "|role:(\w+)|",
                            function ($matches) {
                                $roles = $this->getRoles();
                                return $roles[$matches[1]] ?? '';
                            },
                            $jsonitem);

                        $item       = json_decode($jsonitem, true);
                        $response   = $this->getAPICall()->doPostRequest('App\\Entity\\' . $entitytype, $item);
                        $statusCode = $response->getStatusCode();
                        if ($statusCode == 201) {
                            $response = json_decode($response->getContent(), true);
                            if (!empty($response['id'])) {
                                $idMapping[$currentLocalid] = $response['id'];
                            }
                        } else {
                            $response->getContent();
                            $error = json_decode($response->getContent(), JSON_PRETTY_PRINT);

                            $this->output->write("<error>$error</error>\n");
                            exit(1);
                        }
                    }
                    $progressBar->finish();
                    $this->output->write("\n");
                }
            }
        }
        $this->output->write("\n");
        return 0;
    }

    /**
     * @param string $keycloakFile
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function importKeycloak(string $keycloakFile): bool
    {
        $bCleanId = true;
        $this->output->write("<info>Processing Keycloak import...</info>\n");

        if (!file_exists($keycloakFile)) {
            $this->output->write("<error>file $keycloakFile does not exists</error>\n");
            return false;
        }

        $keycloakJSON = file_get_contents($keycloakFile);

        $adminKc = new KeycloakConnector(
            $this->getGlobals()->getKcUrl(),
            ['username' => $this->kc_admin, 'password' => $this->kc_adminpwd],
            'admin-cli',
            'master'
        );

        // remove all ids
        if ($bCleanId) {
            $keycloakJSON = json_encode(KeycloakConnector::filterNotId(json_decode($keycloakJSON, true)),
                                        JSON_PRETTY_PRINT);
            $keycloakJSON = json_encode(KeycloakConnector::filterNotNull(json_decode($keycloakJSON, true)),
                                        JSON_PRETTY_PRINT);
        }

        $adminKc->partialImport($this->getGlobals()->getKcRealm(), $keycloakJSON);


        $this->output->write("\n");
        return true;

    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isJson(string $string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function purgeCaches()
    {
        $varnishUrl = $_ENV['VARNISHURL'] ?? '';

        if (!empty($varnishUrl)) {
            $this->getbrowserClient()->request('BAN', $varnishUrl,
                                               ['headers' => ['ban-host' => $this->getGlobals()->getFqdn()]]);
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function resetPasswordToUsername()
    {
        $keycloakAdminClient = KeycloakClient::factory([
                                                           'realm'     => 'master',
                                                           'username'  => $this->kc_admin,
                                                           'password'  => $this->kc_adminpwd,
                                                           'client_id' => 'admin-cli',
                                                           'baseUri'   => $this->getGlobals()->getKcUrl(),
                                                           'Accept'    => 'application/json, text/plain'
                                                       ]);
        $users               = $keycloakAdminClient->getUsers(['realm' => $this->getGlobals()->getKcRealm()]);
        $this->output->write("\n\t<info>setting user default password</info>\n");
        $adminKc = new KeycloakConnector(
            $this->getGlobals()->getKcUrl(),
            ['username' => $this->kc_admin, 'password' => $this->kc_adminpwd],
            'admin-cli',
            'master'
        );

        foreach ($users as $currentUser) {
            if ($currentUser['username'] !== 'superuser') {
                $adminKc->setUserPassword($currentUser['id'], $this->getGlobals()->getKcRealm(),
                                          strtolower($currentUser['username']));
                $this->output->write("<comment>\t\t" . $currentUser['username'] . "</comment>\n");
            }
        }
    }

    /**
     *
     */
    public function syncKeycloakRoleToCockpit(): array
    {
        $token = new AnonymousToken('dummy', 'dummy', ['ROLE_Superuser']);
        $this->globals->get('security.token_storage')->setToken($token);
        $cockpitRoles = [];
        $kroles       = $this->getKcRoles();
        foreach ($kroles as $krole) {
            if (empty($this->getEntityManager()->getRepository(Role::class)->find($krole['id']))) {
                if (KeycloakConnector::isCockpitRole($krole['name'])) {
                    $role = new Role();
                    $role->setName(KeycloakConnector::toCockpitRole($krole['name']))
                         ->setSystem(true)
                         ->setId($krole['id'])
                         ->setDescription($krole['description'] ?? '');
                    $this->getEntityManager()->persist($role);
                    $cockpitRoles[$krole['name']] = $role->getId();
                }
            }
        }
        $this->getEntityManager()->flush();

        return $cockpitRoles;
    }
}
