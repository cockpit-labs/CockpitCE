<?php
/*
 * Core
 * APICall.php
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

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class APICall
{

    /**
     * @var HttpClientInterface|Client
     */
    private HttpClientInterface|Client $browserClient;
    /**
     * @var string
     */
    private string $username = '';
    /**
     * @var string
     */
    private string $password = '';
    /**
     * @var array
     */
    private array $superuser = [];
    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;
    /**
     * @var string
     */
    private string $fakedate = '';
    /**
     * @var array[]
     */
    private array $headers = [];
    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var bool
     */
    private bool $coreCall = false;
    /**
     * @var string
     */
    private string $cockpitClient;

    /**
     * @param                                 $httpClient
     * @param \App\Service\ApplicationGlobals $globals
     */
    public function __construct(ApplicationGlobals $globals)
    {
        $this->globals   = $globals;
        $this->superuser = [
            'username' => 'superuser',
            'password' => $_ENV['KEYCLOAK_PASSWORD']
        ];
    }

    /**
     * @return string
     */
    public function getCockpitClient(): string
    {
        return $this->cockpitClient;
    }

    /**
     * @param $browserClient
     *
     * @return $this
     */
    public function setHttpClient($browserClient): APICall
    {
        $this->browserClient = $browserClient;
        return $this;
    }

    public function doRequest(
        string $iri,
        string $operation,
        array  $options = null,
        string $file = ''
    ): ResponseInterface {
        if (empty($options)) {
            $options = $this->headers;
        }
        $url = $this->getGlobals()->getCoreUrl() . $iri;

        if (!empty($this->getFakedate())) {
            $options['headers']['X-FAKETIME'] = $this->getFakedate();
        }

        if (!empty($file)) {
            $hdrs['headers']['content-type'] = 'multipart/formdata';
            $formFields = [
                'file' => DataPart::fromPath($file)
            ];

            $formData = new FormDataPart($formFields);
            $formData->getHeaders()->addTextHeader('Authorization', 'Bearer ' . $this->getToken());

            $hdrs               = $formData->getPreparedHeaders()->toArray();
            $hdrs['X-FAKETIME'] = $this->getFakedate();
            $options            = ['headers' => $hdrs, 'body' => $formData->bodyToString(),];
        }
        return $this->getbrowserClient()->request($operation, $url, $options);

    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCoreToken(): string
    {
        return $this->getGlobals()->getKcCore()->getToken();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getToken(): string
    {
        if ($this->isCoreCall()) {
            return $this->getCoreToken();
        } else {
            return $this->getGlobals()->getKc($this->getCockpitClient(),
                                              $this->getUsername(),
                                              $this->getPassword())->getToken();
        }
    }

    public function refreshToken(): string
    {
        if ($this->isCoreCall()) {
            return $this->getGlobals()->getKcCore()->refreshToken();
        } else {
            return $this->getGlobals()->getKc($this->getCockpitClient(),
                                              $this->getUsername(),
                                              $this->getPassword())->refreshToken();
        }
    }
    /**
     * @param        $class
     * @param string $id
     * @param array  $params
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doDeleteRequest($class, string $id, array $params = []): ResponseInterface
    {
        $this->initRequest();
        $opt = $this->headers;
        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
        $iri = "$iri/$id";
        if (!empty($params)) {
            $opt['query'] = $params;
        }
        return $this->doRequest($iri, 'DELETE', $opt);
    }

    /**
     * @param string      $class
     * @param string|null $id
     * @param string|null $additionnalRoute
     * @param array       $params
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doGetRequest(
        string  $class,
        ?string $id = '',
        ?string $additionnalRoute = '',
        array   $params = []
    ): ResponseInterface {
        $this->initRequest();
        $opt = $this->headers;
        if (is_array($id)) {
            $iri = static::findIriBy($class, $id);
        } else {
            $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
            if (!empty($id)) {
                $iri = "$iri/$id";
            }
        }
        $iri = rtrim($iri . "/$additionnalRoute", '/');
        if (!empty($params)) {
            $opt['query'] = $params;
        }
        return $this->doRequest($iri, 'GET', $opt);
    }

    /**
     * @param $class
     * @param $id
     * @param $data
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doPatchRequest($class, $id, $data): ResponseInterface
    {
        $this->initRequest();
        $hdrs                            = $this->headers;
        $hdrs['headers']['content-type'] = 'application/merge-patch+json';
        $opt                             = array_merge($hdrs, ['body' => json_encode($data)]);

        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
        return $this->doRequest("$iri/$id", 'PATCH', $opt);
    }

    /**
     * @param $class
     * @param $id
     * @param $data
     * @param $action
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doPatchWithActionRequest($class, $id, $data, $action): ResponseInterface
    {
        $this->initRequest();
        $hdrs                            = $this->headers;
        $hdrs['headers']['content-type'] = 'application/merge-patch+json';
        $opt                             = array_merge($hdrs, ['body' => json_encode($data)]);

        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
        return $this->doRequest("$iri/$id/$action", 'PATCH', $opt);
    }

    /**
     * @param string $class
     * @param array  $data
     * @param string $additionnalRoute
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doPostRequest(string $class, array $data, string $additionnalRoute = ""): ResponseInterface
    {
        $this->initRequest();
        $opt = array_merge($this->headers, ['body' => json_encode($data)]);
        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
        $iri = rtrim($iri . "/$additionnalRoute", '/');
        return $this->doRequest($iri, 'POST', $opt);
    }

    /**
     * @param $class
     * @param $data
     * @param $action
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doPostRequestWithAction($class, $data, $action): ResponseInterface
    {
        $this->initRequest();
        $opt = array_merge($this->headers, ['body' => json_encode($data)]);
        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class) . '/' . $action;
        return $this->doRequest($iri, 'POST', $opt);
    }

    /**
     * @param $class
     * @param $file
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Exception
     */
    public function doUploadFileRequest($class, $file): ResponseInterface
    {
        $this->initRequest();
        $hdr = $this->headers;

        $iri = $this->getGlobals()->getIriConverter()->getIriFromResourceClass($class);
        return $this->doRequest($iri, 'POST', $hdr, $file);
    }

    /**
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface|\ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client
     */
    public function getBrowserClient(): HttpClientInterface|Client
    {
        return $this->browserClient;
    }

    /**
     * @param string $client
     *
     * @return APICall
     */
    public function setClient(string $client): APICall
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getFakedate(): string
    {
        return $this->fakedate;
    }

    /**
     * @param string $fakedate
     *
     * @return $this
     */
    public function setFakedate(string $fakedate): APICall
    {
        $this->fakedate = $fakedate;
        return $this;
    }

    /**
     * @return \App\Service\ApplicationGlobals
     */
    public function getGlobals(): ApplicationGlobals
    {
        return $this->globals;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return APICall
     */
    public function setPassword(string $password): APICall
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return APICall
     */
    public function setUsername(string $username): APICall
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function initRequest(): void
    {
        $mimeJSON      = 'application/json';
        $this->options = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getToken(),
            'CONTENT_TYPE'       => $mimeJSON,
        ];
        $this->headers = [
            'headers' => [
                'content-type'  => $mimeJSON,
                'accept'        => $mimeJSON,
                'Authorization' => 'Bearer ' . $this->getToken(),
                'timeout'       => 120
            ]
        ];
    }

    /**
     * @return bool
     */
    public function isCoreCall(): bool
    {
        return $this->coreCall;
    }

    /**
     * @return $this
     */
    public function setAdminClient(): self
    {
        $this->setCockpitClient('cockpitadmin');
        return $this;
    }

    /**
     * @param string $cockpitClient
     *
     * @return $this
     */
    public function setCockpitClient(string $cockpitClient): self
    {
        $this->cockpitClient = $cockpitClient;
        $this->token         = null;
        return $this;
    }

    /**
     * @param bool $coreCall
     *
     * @return APICall
     */
    public function setCoreCall(bool $coreCall): APICall
    {
        $this->coreCall = $coreCall;
        if ($coreCall) {
            $this->setCockpitClient('cockpitcore');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setStudioClient(): self
    {
        $this->setCockpitClient('cockpitstudio');
        return $this;
    }

    /**
     * @return $this
     */
    public function setViewClient(): self
    {
        $this->setCockpitClient($this->getGlobals()->getKcClient('view'));
        return $this;
    }

}
