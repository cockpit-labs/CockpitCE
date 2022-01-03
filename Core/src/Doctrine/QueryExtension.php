<?php
/*
 * Core
 * QueryExtension.php
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

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\CentralAdmin\KeycloakConnector;
use App\DataProvider\CommonDataProvider;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

final class QueryExtension extends CommonDataProvider implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $resourceClass
     */
    private function TargetAddWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $userId = $this->getUserId();
        $queryBuilder->distinct(true)
                     ->andWhere("$rootAlias.ownerId='$userId'");
    }

    /**
     *
     * add SQL Constraint for cockpitview Keycloak Client
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $resourceClass
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $allClients = false;
        if (in_array($resourceClass, [Role::class, User::class])) {
            $allClients = true;
        }

        if ($this->getUser() != null && (in_array($this->getUser()->getClient(), [
                $this->getGlobals()->getKcClient('view'),
                $this->getGlobals()->getKcClient('mobile')
            ])) || $allClients) {
            $addWhereMethod = explode('\\', $resourceClass);
            $addWhereMethod = end($addWhereMethod) . "AddWhere";
            if (method_exists($this, $addWhereMethod)) {
                $this->$addWhereMethod($queryBuilder, $resourceClass);
            }
        }
    }

    /**
     *
     * add filters for Folder
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $resourceClass
     */
    private function folderAddWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->getCockpitUser();

        // get lower groups
        $childTargets = [];
        foreach ($user->getChildGroups() as $childGroup) {
            $childTargets[] = sprintf("'%s'", $childGroup->getId());
        }
        if(empty($childTargets)){
            $queryBuilder->andWhere("0=1");
            return;
        }
        $childTargets    = implode(',', $childTargets);
        $currentUserName = $user->getUsername();

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->distinct(true)
                     ->andWhere("$rootAlias.deletedAt is NULL")
                     ->andWhere("$rootAlias.appliedTo in ($childTargets)")
                     ->join("$rootAlias.folderTpl", "t")->andWhere("t.id=$rootAlias.folderTpl")
                     ->join("t.permissions", "permissions")
                     ->join('permissions.userRole', 'ur')
                     ->join('ur.legacyUsers', 'u')
                     ->andWhere("u.id='" . $user->getId() . "'")
                     ->andWhere("$rootAlias.state!='DRAFT'");

        $queryBuilder->orWhere("$rootAlias.createdBy='$currentUserName' ")
                     ->andWhere("$rootAlias.deletedAt is NULL");
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $resourceClass
     */
    private function folderTplAddWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // endpoint /folder_Tpls is used for stats
        // so, always filter on STATS right
        $user = $this->getCockpitUser();

        // get lower groups
        $childGroups = implode(',', array_map(function ($val) {
            return sprintf("'%s'", $val->getId());
        }, $user->getChildGroups()));

        if(empty($childGroups)){
            $queryBuilder->andWhere("0=1");
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->distinct(true);

        $queryBuilder->join("$rootAlias.permissions", "permissions")
                     ->join("permissions.targetRole", "targetRole")
                     ->join("targetRole.groups", 'g')->andWhere("g.id in ($childGroups)");

    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $resourceClass
     */
    private function userAddWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if($this->getUser()->getClient() !== $this->getGlobals()->getKcClient('admin')){
            $queryBuilder->andWhere("$rootAlias.enabled = 1");
        }
        $s=$queryBuilder->getQuery()->getSQL();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder                                             $queryBuilder
     * @param \ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface $queryNameGenerator
     * @param string                                                                 $resourceClass
     * @param string|null                                                            $operationName
     */
    public function applyToCollection(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        string                      $operationName = null
    ) {
        if ($this->getUser() === null) {
            return;
        }

        $this->operationName = $operationName;
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder                                             $queryBuilder
     * @param \ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface $queryNameGenerator
     * @param string                                                                 $resourceClass
     * @param array                                                                  $identifiers
     * @param string|null                                                            $operationName
     * @param array                                                                  $context
     */
    public function applyToItem(
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        array                       $identifiers,
        string                      $operationName = null,
        array                       $context = []
    ) {
    }

}
