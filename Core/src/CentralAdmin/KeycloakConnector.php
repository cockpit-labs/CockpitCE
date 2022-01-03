<?php
/*
 * Core
 * KeycloakConnector.php
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

namespace App\CentralAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class KeycloakConnector
 *
 * @package App\CentralAdmin
 */
class KeycloakConnector
{

    public const NOLEGACYMEMBERSHIP = 'NO';
    public const UPMEMBERSHIP       = 'UP';
    public const DOWNMEMBERSHIP     = 'DOWN';

    public const  KCSUBGROUPS  = "subGroups";
    public const  KCREALMROLES = "realmRoles";

    private const BEARER  = "Bearer ";
    private const BASEURL = "/auth/admin/realms/";

    private const RESOURCE_USERS        = '/users';
    private const RESOURCE_ROLES        = '/roles';
    private const RESOURCE_GROUPS       = '/groups';
    private const RESOURCE_ROLEMAPPINGS = '/role-mappings/realm';
    private const HEADER_CONTENTTYPE    = 'application/json;charset=UTF-8';
    private const HEADER_ACCEPT         = 'application/json, text/plain,*/*';

    /**
     * @var array
     */
    private $flatGroups = [];

    private $keycloakSecret = '';
    private $username       = '';
    private $password       = '';
    /**
     * @var array
     */
    private $tree = [];
    /**
     * @var string
     */
    private $token = "";

    /**
     * @var array
     */
    private array $reqOptions = [];
    /**
     * @var array
     */
    private $representations;
    /**
     * @var int
     */
    private int $lastError;

    /**
     * keycloakConnector constructor.
     *
     * @param String $keycloakUrl
     * @param        $keycloakSecret
     * @param String $keycloakClient
     * @param String $keycloakRealm
     *
     * @throws \Exception
     */
    public function __construct(
        string $keycloakUrl,
        $keycloakSecret,
        string $keycloakClient,
        string $keycloakRealm
    ) {
        // set keycloak env
        if (is_array($keycloakSecret) && !empty($keycloakSecret['username']) && !empty($keycloakSecret['password'])) {
            $this->username = $keycloakSecret['username'];
            $this->password = $keycloakSecret['password'];
        } else {
            $this->keycloakSecret = $keycloakSecret;
        }
        $this->keycloakUrl       = $keycloakUrl;
        $this->keycloakClient    = $keycloakClient;
        $this->keycloakRealm     = $keycloakRealm;
        $this->keycloakApiPath   = $keycloakUrl . static::BASEURL . $this->keycloakRealm;
        $this->keycloakTokenPath =
            $keycloakUrl . '/auth/realms/' . $this->keycloakRealm . '/protocol/openid-connect/token';

        // Get Core token from KeyCloak
        $this->requestToken($this->keycloakClient, $this->username, $this->password);

        // init http client
        if ($_ENV['APP_ENV'] == 'test' || $_ENV['APP_ENV'] == 'dev') {
            $this->reqOptions['verify'] = false;
        }

        $this->reqOptions['base_uri'] = $keycloakUrl;
        $this->reqOptions['headers']  = ['Authorization' => static::BEARER . $this->token];
        $this->httpClient             = new Client($this->reqOptions);

        // init groups
        $this->childrenGroups = [];
    }

    /**
     * @param string      $resource
     * @param string      $operation
     * @param string|null $id
     * @param string|null $subResource
     * @param array       $param
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function callAdminAPI(
        string $resource,
        string $operation = 'GET',
        string $id = null,
        string $subResource = null,
        array $param = []
    ): array {
        if (!empty($resource)) {
            $route = $this->keycloakApiPath . "/$resource";
            if (!empty($id)) {
                $route .= "/$id";
                if (!empty($subResource)) {
                    $route .= "/$subResource";
                }
            }
            if (!empty($param)) {
                $route .= '?';
                foreach ($param as $name => $value) {
                    $route .= "$name=$value&";
                }
                $route = rtrim($route, "&");
            }
        } else {
            return [];
        }

        try {
            $response = $this->httpClient->request(
                $operation,
                $route
            );
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return [];
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function refreshToken(): string
    {
        return $this->requestToken($this->keycloakClient, $this->username, $this->password);
    }

    /**
     * @param String $rootGroup
     *
     * @return array
     */
    private function callGetGroups(string $rootGroup = ""): array
    {
        // get group list from KeyCloak
        return $this->callAdminAPI('groups', 'GET', $rootGroup, null,
                                   ['briefRepresentation' => false]);
    }

    /**
     * @param String $userId
     *
     * @return array
     */
    private function callGetUserGroups(string $userId): array
    {
        // get user's group list from KeyCloak
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . static::RESOURCE_GROUPS
            );
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return [];
        }
    }

    private function cleanRepresentation(string $representationName, $data)
    {
        if (empty($this->representations)) {
            $rep                   = file_get_contents(__DIR__ . '/keycloak-objects.json');
            $this->representations = json_decode($rep, true);
        }

        $data = static::filterNotNull($data);
        if (isset($this->representations[$representationName])) {
            return array_intersect_key($data, $this->representations[$representationName]);
        }
        return $data;
    }

    private function clearCache(): bool
    {
        $url = $this->keycloakApiPath . '/clear-realm-cache';
        try {
            $options = $this->reqOptions;
            $this->httpClient->request(
                'POST',
                $url,
                $options
            );
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }


    }

    /**
     * @param array $groups
     *
     * @return array
     */
    private function enhanceGroups(array $groups)
    {
        $enhGroups = [];
        $allGroups = $this->getFlatGroups();
        foreach ($groups as &$group) {
            if (!empty($allGroups[$group['id']])) {
                $enhGroups[$group['id']] = $allGroups[$group['id']];
            }
        }
        return $enhGroups;
    }

    /**
     * @param array       $group
     * @param string|null $parentId
     * @param array       $parentRoles
     */
    private function extendTree(array &$group, string $parentId = null, $parentRoles = []): void
    {
        if (isset($group[KeycloakConnector::KCSUBGROUPS])) {
            foreach ($group[KeycloakConnector::KCSUBGROUPS] as $id => $subGroup) {
                $roles = array_merge($parentRoles, $group['realmRoles']);
                $this->extendTree($group[KeycloakConnector::KCSUBGROUPS][$id], $group['id'], $roles);
            }
        }
        $flatGroup                     = $group;
        $flatGroup['parent']           = $parentId;
        $flatGroup[self::KCREALMROLES] = array_merge($parentRoles, $flatGroup[self::KCREALMROLES]);
        $flatGroup['heritedRoles']     = $parentRoles;
        $flatGroup['type']             = 'GROUP';
        unset($flatGroup[KeycloakConnector::KCSUBGROUPS]);
        $this->flatGroups[$flatGroup['id']] = $flatGroup;
    }

    /**
     * @return bool
     */
    private function getFullGroupsTree(): bool
    {
        // get all groups from keycloak
        $rootGroups = $this->callGetGroups();

        // remove subGroups from root groups
        $rootGroups = array_map(function ($group) {
            unset($group[KeycloakConnector::KCSUBGROUPS]);
            return $group;
        }, $rootGroups);

        // get complete groups with roles
        // and flatten tree
        foreach ($rootGroups as &$rootGroup) {
            $rootGroup = $this->callGetGroups($rootGroup['id']);
            $this->extendTree($rootGroup);
        }
        $this->tree = [
            'id'                            => 'root',
            'name'                          => 'root',
            'path'                          => '/',
            KeycloakConnector::KCREALMROLES => null,
            KeycloakConnector::KCSUBGROUPS  => $rootGroups
        ];

        return true;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    private function isValidId(string $id): bool
    {
        return Uuid::isValid($id);
    }

    /**
     * @param array $group
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addGroup(array $group): string
    {
        $url = $this->keycloakApiPath . static::RESOURCE_GROUPS;
        if (!empty($group['parent'])) {
            $parentId = $group['parent'];
            $url      = $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $parentId . '/children';
        }
        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('group', $group);
            $response        = $this->httpClient->request(
                'POST',
                $url,
                $options
            );
            $h               = $response->getHeader('Location');
            $id              = basename($h[0]);
            $this->clearCache();
            $this->setFlatGroups([]);
            return $id;
        } catch (ClientException $e) {

            $this->setLastError($e->getCode());
            return false;
        }

    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addRole(string $name, string $description = ''): bool
    {
        $name = self::toKeycloakRole($name);
        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation(
                'role',
                ['name' => $name, 'description' => $description]
            );
            $response        = $this->httpClient->request(
                'POST',
                $this->keycloakApiPath . static::RESOURCE_ROLES,
                $options
            );
            $this->clearCache();
            return $response->getStatusCode() == 201;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }
    }

    /**
     * @param array $user
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addUser(array $user): bool
    {
        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('user', $user);
            $response        = $this->httpClient->request(
                'POST',
                $this->keycloakApiPath . static::RESOURCE_USERS,
                $options
            );
            $this->clearCache();
            return $response->getStatusCode() == 201;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteGroup(string $id): bool
    {
        try {
            $options = $this->reqOptions;
            $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $id,
                $options
            );
            $this->clearCache();
            $this->setFlatGroups([]);
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }
    }

    /**
     * @param string $groupId
     * @param array  $role
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteGroupRole(string $groupId, array $role): bool
    {
        $r['name'] = self::toKeycloakRole($role['name']);
        $r['id']   = $role['id'];
        try {
            $options         = $this->reqOptions;
            $options['json'] = [$r];
            $response        = $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $groupId . static::RESOURCE_ROLEMAPPINGS,
                $options
            );
            $this->clearCache();
            return $response->getStatusCode() == 204;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteRole(string $id): bool
    {
        $id = self::toKeycloakRole($id);
        try {
            $options = $this->reqOptions;
            $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_ROLES . '/' . $id,
                $options
            );
            $this->clearCache();
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteUser(string $id): bool
    {
        try {
            $options = $this->reqOptions;
            $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $id,
                $options
            );
            $this->clearCache();
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }
    }

    /**
     * @param string $userId
     * @param array  $group
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteUserGroup(string $userId, array $group): bool
    {
        $groupId = $group['id'];
        try {
            $options  = $this->reqOptions;
            $response = $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . '/groups/' . $groupId,
                $options
            );
            $this->clearCache();
            return $response->getStatusCode() == 204;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }
    }

    /**
     * @param $userId
     * @param $role
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteUserRole($userId, $role): bool
    {
        $r['name'] = self::toKeycloakRole($role['name']);
        $r['id']   = $role['id'];
        try {
            $options         = $this->reqOptions;
            $options['json'] = [$r];
            $response        = $this->httpClient->request(
                'DELETE',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . static::RESOURCE_ROLEMAPPINGS,
                $options
            );
            $this->clearCache();
            return $response->getStatusCode() == 204;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }
    }

    /**
     * @param $userId
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteAllUserRoles($userId): bool
    {
        $roles=$this->getUserRoles($userId);
        foreach ($roles as $role){
            if(!$this->deleteUserRole($userId, $role)){
                return false;
            }
        }
        return true;
    }

    public static function filterCockpitRole(array $array, bool $bNotSystem = true): array
    {
        return array_values(array_filter($array, function ($item) use ($bNotSystem) {
            $ret = false;
            if (isset($item['name'])) {
                if (static::isCockpitRole($item['name']) && $bNotSystem) {
                    if (isset($item['attributes']['system'][0]) && $item['attributes']['system'][0]) {
                        $ret = false;
                    } else {
                        $ret = true;
                    }
                }
            }
            return $ret;
        }));
    }

    public static function filterNotId(array $array): array
    {
        $array = array_map(function ($item) {
            return is_array($item) ? static::filterNotId($item) : $item;
        }, $array);
        return array_filter($array, function ($k) {
            return ($k !== 'id');
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function filterNotNull(array $array): array
    {
        $array = array_map(function ($item) {
            return is_array($item) ? static::filterNotNull($item) : $item;
        }, $array);
        return array_filter($array, function ($item) {
            return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
        });
    }

    /**
     * @param array $group
     *
     * @return array
     */
    public function getChildrenGroups(array $group): array
    {
        $refPath = $group['path'];

        $func   = function ($group) use ($refPath) {
            return substr($group['path'], 0, strlen($refPath)) === $refPath;
        };
        $groups = array_filter($this->getFlatGroups(), $func);
        usort($groups, function ($a, $b) {
            return $a['path'] <=> $b['path'];
        });
        return $groups;
    }

    /**
     * @return array
     */
    public function getFlatGroups(): array
    {
        $this->getFullGroupsTree();
        // sort groups by path
        uasort($this->flatGroups, function ($a, $b) {
            return ($a['path'] < $b['path'] ? -1 : 1);
        });
        return $this->flatGroups;
    }

    /**
     * @param array $flatGroups
     */
    public function setFlatGroups(array $flatGroups): void
    {
        $this->flatGroups = $flatGroups;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getGroup(string $id): array
    {
        if ($this->isValidId($id) && array_key_exists($id, $this->getFlatGroups())) {
            return $this->getFlatGroups()[$id];
        }
        return [];
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function getGroupByPath($path): array
    {
        $func   = function ($group) use ($path) {
            return $group['path'] == $path;
        };
        $groups = array_values(array_filter($this->getFlatGroups(), $func));
        if (!empty($groups)) {
            return $groups[0];
        }
        return [];
    }

    /**
     * @param string $groupPath
     *
     * @return string
     */
    public function getGroupId(string $groupPath): string
    {
        $id    = "";
        $group = array_filter(
            $this->getFlatGroups(),
            function ($g) use (&$groupPath) {
                return $g['path'] === $groupPath;
            }
        );

        if (!empty($group)) {
            $id = key($group);
        }
        return $id;
    }

    /**
     * @param string $groupId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getGroupMembers(string $groupId): array
    {
        if ($this->isValidId($groupId)) {
            return $this->callAdminAPI('groups', 'GET', $groupId, 'members');
        }
        return [];
    }

    /**
     * @param array $group
     *
     * @return string
     */
    public function getGroupPath(array $group): string
    {
        if (!empty($group['path'])) {
            return $group['path'];
        }

        if (!empty($group['id'])) {
            $group = $this->getGroup($group['id']);
            return $group['path'];
        }

        if (!empty($group['name'])) {
            $parentPath = '';
            if (!empty($group['parent'])) {
                $parent     = $this->getGroup($group['parent']);
                $parentPath = $parent['path'];
            }
            return $parentPath . '/' . $group['name'];
        }
        return '';
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getGroupRoles(string $id): array
    {
        if ($this->isValidId($id)) {
            $roles =
                $this->callAdminAPI(
                    'groups',
                    'GET',
                    $id,
                    'role-mappings/realm/composite',
                    ['briefRepresentation' => 'false']
                );
            // remove non Cockpit roles
            $roles = array_filter($roles, function ($role) {
                return self::isCockpitRole($role['name']);
            });
            // fix roles name
            $roles = array_map(function ($role) {
                $role['name'] = self::toCockpitRole($role['name']);
                return $role;
            }, $roles);
            return array_values($roles);
        } else {
            return [];
        }
    }

    /**
     * @return int
     */
    public function getLastError(): int
    {
        return $this->lastError;
    }

    /**
     * @param array $group
     *
     * @return array
     */
    public function getParentGroup(array $group): array
    {
        $parentPath = basename($group['path']);
        return $this->getGroupByPath($parentPath);
    }

    /**
     * @param array $group
     *
     * @return array
     */
    public function getParentGroups(array $group): array
    {
        $refPath = $group['path'];

        $func   = function ($group) use ($refPath) {
            return (substr($refPath, 0, strlen($group['path'])) === $group['path']) && ($group['path'] != $refPath);
        };
        $groups = array_filter($this->getFlatGroups(), $func);
        usort($groups, function ($a, $b) {
            return $a['path'] <=> $b['path'];
        });
        return $groups;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getRoleById(string $id): array
    {
        $role = $this->callAdminAPI('roles-by-id', 'GET', $id, null,
                                    ['briefRepresentation' => false]);
        if (!empty($role)) {
            $role['name'] = self::toCockpitRole($role['name']);
        }
        return $role;
    }

    /**´´
     *
     * @param string|null $name
     *
     * @return array
     */
    public function getRoleByName(string $name = null): array
    {
        $name  = self::toKeycloakRole($name);
        $roles = array_filter(
            $this->callAdminAPI(
                'roles',
                'GET',
                null,
                null,
                ['briefRepresentation' => false]
            ),
            function ($value) use ($name) {
                return $value['name'] == $name;
            }
        );
        $role  = array_values($roles)[0] ?? [];
        if (!empty($role)) {
            $role['name'] = self::toCockpitRole($role['name']);
        }
        return $role;
    }

    public function getRoleGroups(string $roleName): array
    {
        $roleName = static::toKeycloakRole($roleName);
        return $this->callAdminAPI('roles', 'GET', $roleName, 'groups');
    }

    public function getRoleUsers(string $roleName): array
    {
        $roleName = static::toKeycloakRole($roleName);
        return $this->callAdminAPI('roles', 'GET', $roleName, 'users');
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->callAdminAPI(
            'roles',
            'GET',
            null,
            null,
            ['briefRepresentation' => false]
        );
        return array_map(function ($role) {
            $role['name'] = self::toCockpitRole($role['name']);
            return $role;
        }, array_filter($roles, function ($role) {
            return self::isCockpitRole($role['name']);
        }));

    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function getTree(): array
    {
        if (empty($this->tree)) {
            $this->getFullGroupsTree();
        }
        return $this->tree;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getUser(string $id): array
    {
        $user = [];
        if ($this->isValidId($id)) {
            $user = $this->callAdminAPI('users', 'GET', $id);
            if (!empty($user)) {
                $user['type'] = 'USER';
            }
        } else {
            // maybe it's a username
            $users = $this->getUsers($id);
            if (!empty($users)) {
                $user = $users[0];
            }
        }
        return $user;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getUserEffectiveRoles(string $id): array
    {
        if ($this->isValidId($id)) {
            $roles =
                $this->callAdminAPI(
                    'users',
                    'GET',
                    $id,
                    'role-mappings/realm/composite',
                    ['briefRepresentation' => 'false']
                );
            // remove non Cockpit roles
            $roles = array_filter($roles, function ($role) {
                return self::isCockpitRole($role['name']);
            });
            // fix roles name
            $roles = array_map(function ($role) {
                $role['name'] = self::toCockpitRole($role['name']);
                return $role;
            }, $roles);
            return array_values($roles);
        } else {
            return [];
        }
    }


    /**
     * @param string $userId
     * @param bool   $children if true, add children groups for each user groups
     * @param bool   $parents  if true, add parent groups for each user groups
     * @param array  $roles    filter users groups with at least one role from this list (children and/or parents
     *                         excluded)
     *
     * @return array
     */
    public function getUserGroups(string $userId, $membershipDirection = self::NOLEGACYMEMBERSHIP, $roles = []): array
    {
        $roles = self::toKeycloakRoles($roles);

        if (empty($userId)) {
            $userGroups = $this->getFlatGroups();
        } else {
            // get user groups
            $userGroups = $this->callGetUserGroups($userId);
            $userGroups = $this->enhanceGroups($userGroups);

            // filter legacy groups
            $getLegacyGroupsFunc = function ($group) use ($userGroups, $membershipDirection) {
                if (!isset($group['path'])) {
                    return false;
                }
                $groupPath = $group['path'];
                foreach ($userGroups as $userGroup) {
                    $path = $userGroup['path'];
                    if (($groupPath === $path)
                        || ($membershipDirection == self::DOWNMEMBERSHIP && substr(
                                $groupPath,
                                0,
                                strlen($path)
                            ) === $path)
                        || ($membershipDirection == self::UPMEMBERSHIP && substr(
                                $path,
                                0,
                                strlen($groupPath)
                            ) === $groupPath)) {
                        return true;
                    }
                }
                return false;
            };
            // add legacy groups
            if ($membershipDirection != self::NOLEGACYMEMBERSHIP) {
                $userGroups = array_filter($this->getFlatGroups(), $getLegacyGroupsFunc);
            }
            $tmpUserGroups = [];
            foreach ($userGroups as $id => $userGroup) {
                if (empty($roles) || count(array_intersect($userGroup['realmRoles'], $roles)) > 0) {
                    $tmpUserGroups[$id] = $userGroup;
                }
            }
            $userGroups = $tmpUserGroups;
        }

        if (!empty($roles)) {
            // add filter role to legacy groups
            $addRoleFunc = function ($group) use ($roles) {
                $group['realmRoles'] = array_unique(array_merge($group['realmRoles'], $roles));
                return $group;
            };
            $userGroups  = array_map($addRoleFunc, $userGroups);
        }
        return $userGroups;
    }

    /**
     * @param string $username
     *
     * @return string
     */
    public function getUserId(string $username): string
    {
        $users = $this->callAdminAPI("users", "GET", null, null, ['search' => $username]);

        if (isset($users[0]['id'])) {
            return $users[0]['id'];
        } else {
            return "";
        }
    }

    /**
     * @param string $id
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserRoles(string $id): array
    {
        if ($this->isValidId($id)) {
            $roles =
                $this->callAdminAPI(
                    'users',
                    'GET',
                    $id,
                    'role-mappings/realm',
                    ['briefRepresentation' => 'false']
                );
            // remove non Cockpit roles
            $roles = array_filter($roles, function ($role) {
                return self::isCockpitRole($role['name']);
            });
            // fix roles name
            $roles = array_map(function ($role) {
                $role['name'] = self::toCockpitRole($role['name']);
                return $role;
            }, $roles);
            return array_values($roles);
        } else {
            return [];
        }
    }

    /**
     * @param string $searchstring
     *
     * @return array
     */
    public function getUsers(string $searchstring): array
    {
        $param = [];
        if (!empty($searchstring)) {
            $param = ['search' => $searchstring];
        }
        return $this->callAdminAPI("users", "GET", null, null, $param);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function groupExists(string $id): bool
    {
        if ($this->isValidId($id)) {
            return !empty($this->getGroup($id));
        }
        return false;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public static function isCockpitRole(string $role): bool
    {
        return preg_match('/^CKP_(.*)/', $role) === 1;
    }

    /**
     * @param string $kcServer
     * @param string $realm
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function isValidRealm(string $kcServer, string $realm): bool
    {
        $url    = $kcServer . '/auth/realms/' . $realm;
        $client = new Client(); //initialize a Guzzle client
        try {
            $client->request('GET', $url);
            return true;
        } catch (ClientException $e) {
            // en cas d'erreur
            return false;
        }
    }

    public function partialExport(string $realm): array
    {
        $url = $this->keycloakUrl . static::BASEURL . $realm . '/partial-export?&exportClients=true&exportGroupsAndRoles=true';

        try {
            $client   = new Client(); //initialize a Guzzle client
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept'        => static::HEADER_ACCEPT,
                    'Authorization' => static::BEARER . $this->token,
                    'Content-Type'  => static::HEADER_CONTENTTYPE,
                ]
            ]);
            $data     = static::filterNotNull(json_decode($response->getBody(), true));
            return $this->cleanRepresentation('partialExport', $data);
        } catch (ClientException $e) {
            // en cas d'erreur
            return [];
        }
    }

    /**
     * @param string $json
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function partialImport(string $realm, string $json): array
    {
        $url = $this->keycloakUrl . static::BASEURL . $realm . '/partialImport';

        try {
            $client   = new Client(); //initialize a Guzzle client
            $response = $client->request('POST', $url, [
                'body'    => $json,
                'headers' => [
                    'Accept'        => static::HEADER_ACCEPT,
                    'Authorization' => static::BEARER . $this->token,
                    'Content-Type'  => static::HEADER_CONTENTTYPE,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // en cas d'erreur
            return [];
        }
    }

    /**
     * @param string $clientApp
     * @param string $user
     * @param string $pwd
     *
     * @return String
     * @throws \Exception
     */
    public function requestToken(string $clientApp = "", string $user = "", string $pwd = ""): string
    {
        try {
            $options['base_uri'] = $this->keycloakUrl;

            if ($_ENV['APP_ENV'] == 'test' || $_ENV['APP_ENV'] == 'dev') {
                $options['verify'] = false;
            }
            $formParams = [
                'form_params' => [
                    'client_id'     => $this->keycloakClient,
                    'client_secret' => $this->keycloakSecret,
                    'grant_type'    => 'client_credentials'
                ]
            ];
            if (!empty($clientApp) && !empty($user) && !empty($pwd)) {
                $formParams = [
                    'form_params' => [
                        'client_id'  => $clientApp,
                        'username'   => $user,
                        'password'   => $pwd,
                        'grant_type' => 'password',
                    ]
                ];
            }
            $client   = new Client($options);
            $response = $client->request(
                'POST',
                $this->keycloakTokenPath,
                $formParams
            );
        } catch (ClientException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        $body        = json_decode($response->getBody());
        $this->token = $body->access_token;
        return $this->token;
    }

    /**
     * @param $groupId
     * @param $role
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setGroupRole($groupId, $role): bool
    {
        $role['name'] = self::toKeycloakRole($role['name']);
        $role['id'];
        try {
            $options         = $this->reqOptions;
            $options['json'] = [$role];
            $response        = $this->httpClient->request(
                'POST',
                $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $groupId . static::RESOURCE_ROLEMAPPINGS,
                $options
            );
            return $response->getStatusCode() == 201;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }

    }

    /**
     * @param int $lastError
     */
    public function setLastError(int $lastError): void
    {
        $this->lastError = $lastError;
    }

    /**
     * @param $userId
     * @param $group
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setUserGroup($userId, $group): bool
    {
        $groupId = $group['id'];
        try {
            $options  = $this->reqOptions;
            $response = $this->httpClient->request(
                'PUT',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . '/groups/' . $groupId,
                $options
            );
            return $response->getStatusCode() == 204;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }

    }

    /**
     * @param string $userid
     * @param string $newpassword
     *
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setUserPassword(string $userid, string $realm, string $newpassword): ?array
    {
        $url = $this->keycloakUrl . static::BASEURL . $realm . static::RESOURCE_USERS . '/' . $userid . '/reset-password';
        try {
            $client   = new Client(); //initialize a Guzzle client
            $response = $client->request('PUT', $url, [
                'json'    => ['type' => 'password', 'temporary' => false, 'value' => $newpassword],
                'headers' => [
                    'Accept'        => static::HEADER_ACCEPT,
                    'Authorization' => static::BEARER . $this->token,
                    'Content-Type'  => static::HEADER_CONTENTTYPE,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // en cas d'erreur
            return [];
        }
    }

    /**
     * @param $userId
     * @param $role
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setUserRole($userId, $role): bool
    {
        $role['name'] = self::toKeycloakRole($role['name']);

        try {
            $options         = $this->reqOptions;
            $options['json'] = [$role];
            $response        = $this->httpClient->request(
                'POST',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . static::RESOURCE_ROLEMAPPINGS,
                $options
            );
            return $response->getStatusCode() == 201;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return false;
        }

    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function toClientRole(string $name): string
    {
        return self::toCockpitRole($name, 'CLIENT_');
    }

    /**
     * @param string $clientRole
     * @param array  $roles
     *
     * @return bool
     */
    public static function clientAuthorized(string $clientRole, array $roles): bool
    {
        $roles = array_map('strtolower', $roles);

        // superuser have all rights
        $superuserRole = strtolower(KeycloakConnector::toSymfonyRole('Superuser'));
        if (array_search(strtolower($superuserRole), $roles) !== false) {
            return true;
        }

        $clientRole = strtolower(KeycloakConnector::toSymfonyRole(ltrim($clientRole, "CLIENT_cockpit")));
        return array_search(strtolower($clientRole), $roles) !== false;
    }

    /**
     * @param string $name
     * @param string $prefix
     *
     * @return string
     */
    public static function toCockpitRole(string $name, $prefix = ''): string
    {
        //  remove any symfony or keycloak prefix
        $patterns    = ['/ROLE_(.*)/', '/CKP_(.*)/'];
        $replacement = array_fill(0, count($patterns), '$1');
        return $prefix . preg_replace($patterns, $replacement, $name);
    }

    /**
     * @param array  $names
     * @param string $prefix
     *
     * @return array
     */
    public static function toCockpitRoles(array $names, $prefix = ''): array
    {
        return array_map(function ($n) use ($prefix) {
            return self::toCockpitRole($n, $prefix);
        }, $names);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function toKeycloakRole(string $name): string
    {
        return self::toCockpitRole($name, 'CKP_');
    }

    /**
     * @param array $names
     *
     * @return string
     */
    public static function toKeycloakRoles(array $names): array
    {
        return array_map(function ($n) {
            return self::toKeycloakRole($n);
        }, $names);
    }

    /**
     * @param String $name
     *
     * @return String
     */
    public static function toSymfonyRole(string $name): string
    {
        return self::toCockpitRole($name, 'ROLE_');
    }

    public function updateGroup(string $id, array $group): bool
    {
        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('group', $group);
            $this->httpClient->request(
                'PUT',
                $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $id,
                $options
            );
            $this->setFlatGroups([]);
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }
    }

    public function updateGroupSetParent(string $id, string $parentId): bool
    {
        $group = $this->getGroup($id);

        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('group', $group);
            $this->httpClient->request(
                'POST',
                $this->keycloakApiPath . static::RESOURCE_GROUPS . '/' . $parentId . '/children',
                $options
            );
            $this->setFlatGroups([]);
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }

    }

    /**
     * @param string $id
     * @param array  $role
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateRole(string $id, array $role): bool
    {
        $id           = self::toKeycloakRole($id);
        $role['name'] = self::toKeycloakRole($role['name']);

        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('role', $role);
            $this->httpClient->request(
                'PUT',
                $this->keycloakApiPath . static::RESOURCE_ROLES . '/' . $id,
                $options
            );
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return true;
        }
    }

    public function updateUser(string $id, array $user)
    {
        if (!$this->isValidId($id)) {
            throw new NotFoundHttpException("user $id not found");
        }
        unset($user['username']);
        try {
            $options         = $this->reqOptions;
            $options['json'] = $this->cleanRepresentation('user', $user);
            $this->httpClient->request(
                'PUT',
                $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $id,
                $options
            );
            return true;
        } catch (ClientException $e) {
            $this->setLastError($e->getCode());
            return [];
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function userExists(string $id): bool
    {
        if ($this->isValidId($id)) {
            return !empty($this->getUser($id));
        } else {
            return !empty($this->getUserId($id));
        }
    }

    /**
     * @param string $userId
     * @param string $returnUrl
     * @param        $clientId
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function welcomeUser(string $userId, string $returnUrl, $clientId): bool
    {
        $url = $this->keycloakApiPath . static::RESOURCE_USERS . '/' . $userId . '/execute-actions-email?redirect_uri' . urlencode($returnUrl . '&client_id=' . $clientId);
        try {
            $client = new Client(); //initialize a Guzzle client
            $client->request('PUT', $url, [
                'body'    => '["VERIFY_EMAIL"]',
                'headers' => [
                    'Accept'        => static::HEADER_ACCEPT,
                    'Authorization' => static::BEARER . $this->token,
                    'Content-Type'  => static::HEADER_CONTENTTYPE,
                ]
            ]);

            return true;
        } catch (ClientException $e) {
            // en cas d'erreur
            return false;
        }

    }
}
