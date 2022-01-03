<?php
/*
 * Core
 * ApplicationGlobals.php
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

namespace App\Service;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\CentralAdmin\KeycloakConnector;
use App\Entity\KeycloakKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class ApplicationGlobals extends AbstractController
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager = null;
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $parameters;
    /**
     * @var string
     */
    private string $kcUrl = '';
    /**
     * @var string
     */
    private string $kcSecret;
    /**
     * @var string[]
     */
    private $kcClients = [
        'view'   => 'cockpitview',
        'admin'  => 'cockpitadmin',
        'studio' => 'cockpitstudio',
        'mobile' => 'cockpitmobile',
        'core'   => 'cockpitcore'
    ];
    /**
     * @var string
     */
    private string $kcRealm = 'cockpit';
    /**
     * @var string
     */
    private string $gotenbergUrl = '';
    /**
     * @var string
     */
    private string $publicKey = '';
    /**
     * @var string
     */
    private string $privateKey = '';
    /**
     * @var string
     */
    private string $dbName = '';
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private ?Request $request;
    /**
     * @var string
     */
    private string $domain = '';
    /**
     * @var string
     */
    private string $kcAuthUrl = '';
    /**
     * @var \ApiPlatform\Core\Api\IriConverterInterface
     */
    private IriConverterInterface $iriConverter;

    /**
     * @var string
     */
    private string $baseUrl = '';

    /**
     * @var string
     */
    private string $host = '';

    /**
     * @var string
     */
    private string $fqdn = '';
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    private Security $security;
    /**
     * @var \App\CentralAdmin\KeycloakConnector
     */
    private KeycloakConnector $kcCore;
    /**
     * @var \App\CentralAdmin\KeycloakConnector
     */
    private KeycloakConnector $kc;


    public function __construct(
        ParameterBagInterface $params,
        RequestStack          $requestStack,
        IriConverterInterface $iriService,
        Security              $security,
        ContainerInterface    $container
    ) {
        $this->container    = $container;
        $this->iriConverter = $iriService;
        $this->parameters   = $params;
        $this->request      = $requestStack->getCurrentRequest();

        $this->gotenbergUrl = $_ENV['GOTENBERGURL'] ?? 'http://gotenberg:3000';
        $this->kcUrl        = $_ENV['KEYCLOAKURL'] ?? 'http://keycloak:8080';
        $this->kcSecret     = $_ENV['JWT_PASSPHRASE'];

        $this->setDomain($_ENV['DOMAIN'] ?? 'localhost');
        $this->setFqdn($_ENV['FQDN'] ?? $this->getDomain());
        $this->parseRequest();

        $this->baseUrl = $_ENV['BASEURL'] ?? '';

        $this->security = $security;
        $this->setDbName('cockpit');
    }

    private function parseRequest(): self
    {
        if (!empty($this->request)) {
            // if it's a command request, do special stuff
            $scheme = $_ENV['SCHEME'] ?? 'https';
            $this->setKcAuthUrl($scheme . '://' . $this->host . '/auth');
            $this->baseUrl = $scheme . '://' . $this->host;
        }
        return $this;
    }

    private function setKeycloakKey(string $type, string $key): ApplicationGlobals
    {

        $keycloakKey = $this->getEntityManager()->find(KeycloakKey::class, $type);
        $keycloakKey = $keycloakKey ?? new KeycloakKey();
        $keycloakKey->setType($type)->setValue($key);
        $this->getEntityManager()->persist($keycloakKey);
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $param
     * @param string                                                                    $name
     * @param string                                                                    $default
     *
     * @return string
     */
    public static function filename(ParameterBagInterface $param, string $name, string $default = ''): string
    {
        $result = $default;
        if (is_a($param, Container::class)) {
            $param = $param->getParameterBag();
        }
        if ((is_a($param, Parameter::class)
                || is_a($param, ParameterBag::class)
                || is_a($param, FrozenParameterBag::class)
                || is_a($param, ContainerBag::class))
            && $param->has($name)) {
            $default = $default ?: $param->get($name);
            $result  = file_exists($param->get($name)) ? $param->get($name) : $default;
        }

        return $result;
    }

    /**
     * @param string $id
     *
     * @return object
     */
    public function get(string $id): object
    {
        return $this->container->get($id);
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (empty($this->baseUrl)) {
            // construct a base url
            $scheme        = $_ENV['SCHEME'] ?? 'https';
            $this->baseUrl = $scheme . '://' . $this->getFqdn();
        }
        return $this->baseUrl;
    }

    public function getClients(): array
    {
        return array_keys($this->kcClients);
    }

    public function getConnectionString(): string
    {

        $url = $_ENV['DATABASE_URL'];

        $regex = '/(.*?:\/\/)(.*?)(:)(.*?)(@)(.*)(:)([[:digit:]]{0,4})(\/?)(.*?)(\?)(.*?)$/';

        if (preg_match($regex, $url, $cnxparams)) {
            $cnxparams[10] = $this->getDbName();
            array_shift($cnxparams);
        } else {
            throw new BadRequestHttpException("bad database configuration");
        }
        return implode('', $cnxparams);
    }

    /**
     * @return string
     */
    public function getCoreUrl(): string
    {
        return $_ENV['APIURL'] ?? 'http://localhost:8888/api';
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     *
     * @return ApplicationGlobals
     */
    public function setDbName(string $dbName): ApplicationGlobals
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        if (empty($this->domain)) {
            $this->domain = 'localhost';
        } else {
            $this->setDomain($_ENV['DOMAIN']);
        }
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return ApplicationGlobals
     */
    public function setDomain(string $domain = ''): ApplicationGlobals
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return ApplicationGlobals
     */
    public function setEntityManager(EntityManagerInterface $entityManager): ApplicationGlobals
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getFqdn(): string
    {
        return $this->fqdn;
    }

    /**
     * @return string
     */
    public function getGotenbergUrl(): string
    {
        return $this->gotenbergUrl;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        if (empty($this->host) && !empty($this->request)) {
            $this->host = strtolower($this->request->getHost());
        }
        return $this->host ?: 'localhost';
    }

    /**
     * @param $iri
     *
     * @return string
     */
    public static function getIdFromIri(string $iri): string
    {
        if (preg_match("/[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}/", $iri,
                       $matches)) {
            return $matches[0];
        }
        return '';
    }

    /**
     * @return \ApiPlatform\Core\Api\IriConverterInterface
     */
    public function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }

    /**
     * @return \App\CentralAdmin\KeycloakConnector
     * @throws \Exception
     */
    public function getKc(string $client, string $username, string $password): KeycloakConnector
    {
        $this->kc = new KeycloakConnector(
            $this->getKcUrl(),
            ['username' => $username, 'password' => $password],
            $client,
            $this->getKcRealm());
        return $this->kc;
    }

    /**
     * @return string
     */
    public function getKcAuthUrl(): string
    {
        if (empty($this->kcAuthUrl)) {
            $this->kcAuthUrl = $this->getBaseUrl() . '/auth';
        }
        return $this->kcAuthUrl;
    }

    /**
     * @param string $kcAuthUrl
     *
     * @return ApplicationGlobals
     */
    public function setKcAuthUrl(string $kcAuthUrl = ''): ApplicationGlobals
    {
        $this->kcAuthUrl = $kcAuthUrl;
        return $this;
    }

    public function getKcClient(string $name): string
    {
        return $this->kcClients[$name] ?? 'none';
    }

    /**
     * @return \App\CentralAdmin\KeycloakConnector
     * @throws \Exception
     */
    public function getKcCore(): KeycloakConnector
    {
        if (empty($this->kcCore)) {
            $this->kcCore = new KeycloakConnector(
                $this->getKcUrl(),
                $this->getKcSecret(),
                $this->getKcCoreClient(),
                $this->getKcRealm());
        }
        return $this->kcCore;
    }

    /**
     * @return string
     */
    public function getKcCoreClient(): string
    {
        return $this->kcClients['core'];
    }

    /**
     * @return string
     */
    public function getKcRealm(): string
    {
        return $this->kcRealm;
    }

    /**
     * @param string $kcRealm
     *
     * @return ApplicationGlobals
     */
    public function setKcRealm(string $kcRealm): ApplicationGlobals
    {
        $this->kcRealm = $kcRealm;
        return $this;
    }

    /**
     * @return string
     */
    public function getKcSecret(): string
    {
        return $this->kcSecret;
    }

    /**
     * @return string
     */
    public function getKcUrl(): string
    {
        return $this->kcUrl;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getKeycloakKey(string $type): string
    {
        $keycloakKey = $this->getEntityManager()->find(KeycloakKey::class, $type);
        if (empty($keycloakKey)) {
            throw new BadRequestHttpException("keycloak $type key not found");
        }
        return $keycloakKey->getValue();
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function getParam(string $name, string $default = ''): string
    {
        $result = $default;
        if (is_a($this->parameters, Container::class)) {
            $param = $this->parameters->getParameterBag();
        } else {
            $param = $this->parameters;
        }
        if ((is_a($param, Parameter::class)
                || is_a($param, ParameterBag::class)
                || is_a($param, FrozenParameterBag::class)
                || is_a($param, ContainerBag::class))
            && $param->has($name)) {
            $result = file_exists($param->get($name)) ? file_get_contents($param->get($name)) : $param->get($name);
            if (empty($result)) {
                $result = $default;
            }
        }

        return $result;

    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (empty($this->privateKey)) {
            $this->privateKey = $this->getKeycloakKey('private');
        }
        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     *
     * @return ApplicationGlobals
     */
    public function setPrivateKey(string $privateKey): ApplicationGlobals
    {
        $this->privateKey = $privateKey;
        return $this->setKeycloakKey('private', $privateKey);
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        if (empty($this->publicKey)) {
            $this->publicKey = $this->getKeycloakKey('public');
        }
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return ApplicationGlobals
     */
    public function setPublicKey(string $publicKey): ApplicationGlobals
    {
        $this->publicKey = $publicKey;
        return $this->setKeycloakKey('public', $publicKey);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return \Symfony\Component\Security\Core\Security
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * @return string
     */
    public function getStudioClient(): string
    {
        return $this->kcClients['studio'];
    }

    /**
     * @return string
     */
    public function getViewClient(): string
    {
        return $this->kcClients['view'];
    }

    /**
     * @param string $clientname
     *
     * @return bool
     */
    public function isClient(string $clientname): bool
    {
        return $this->getUser()->getClient() == $this->kcClients[$clientname];
    }

    /**
     * @return bool
     */
    public function isCoreClient(): bool
    {
        return $this->isClient('core');
    }

    /**
     * @return bool
     */
    public function isMobileClient(): bool
    {
        return $this->isClient('mobile');
    }

    /**
     * @return bool
     */
    public function isStudioClient(): bool
    {
        return $this->isClient('studio');
    }

    /**
     * @return bool
     */
    public function isViewClient(): bool
    {
        return $this->isClient('view');
    }

    /**
     * @param        $param
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public static function param($param, string $name, string $default = ''): string
    {
        $result = $default;
        if (is_a($param, Container::class)) {
            $param = $param->getParameterBag();
        }
        if ((is_a($param, Parameter::class)
                || is_a($param, ParameterBag::class)
                || is_a($param, FrozenParameterBag::class)
                || is_a($param, ContainerBag::class))
            && $param->has($name)) {
            $result = file_exists($param->get($name)) ? file_get_contents($param->get($name)) : $param->get($name);
            if (empty($result)) {
                $result = $default;
            }
        }

        return $result;
    }

    /**
     * @param string $fqdn
     *
     * @return ApplicationGlobals
     */
    public function setFqdn(string $fqdn): ApplicationGlobals
    {
        $this->fqdn = $fqdn;
        return $this;
    }

    /**
     * @return bool
     */
    public function welcomeUser(): bool
    {
        $welcome = $_ENV['WELCOMEUSER'] ?? false;
        if (strtolower($welcome) === 'true' || $welcome === '1') {
            $welcome = true;
        } else {
            $welcome = false;
        }
        return $welcome;
    }

}
