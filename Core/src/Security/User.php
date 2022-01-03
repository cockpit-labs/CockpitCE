<?php
/*
 * Core
 * User.php
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

namespace App\Security;

use App\CentralAdmin\KeycloakConnector;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class User implements JWTUserInterface
{
    public $id;
    private $username;
    private $roles;
    private $client;
    private $locale;

    public function __construct($id, $username, array $roles, $email, $client, $locale)
    {
        $this->username = $username;
        $this->id       = $id;
        $this->roles    = $roles;
        $this->email    = $email;
        $this->client   = $client;
        $this->locale   = $locale;
    }

    public static function createFromPayload($username, array $payload)
    {
        if(!empty($payload)) {
            $r = $payload['realm_access'];
            if (is_object($r)) {
                $roleList = $r->roles;
            } else {
                $roleList = $r['roles'] ?? [];
            }
            $roles=array_map('App\CentralAdmin\KeycloakConnector::toSymfonyRole', $roleList);
            // add special role asssociated to keycloak with client (related to frontend client)
            // remove 'cockpit' from client name before transform to client role
            // a client role is like 'CLIENT_Studio'
            $client = KeycloakConnector::toClientRole(str_replace('cockpit', '', $payload['azp']));
            if (!KeycloakConnector::clientAuthorized($client, $roles)) {
                throw new AccessDeniedHttpException();
            }

            $locale = 'en';
            if (isset($payload['locale'])) {
                $locale = $payload['locale'];
            }
            return new self(
                $payload['sub'],
                $username,
                $roles,
                $payload['email'] ?? '',
                $payload['azp'],
                $locale
            );
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
