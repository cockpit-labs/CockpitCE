<?php
/*
 * Core
 * UserGroupRoleSubscriber.php
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


namespace App\EventListener\UserGroupRole;

use App\CentralAdmin\KeycloakConnector;
use App\Entity\Folder\FolderTpl;
use App\Entity\Group;
use App\Entity\Permission;
use App\Entity\Right;
use App\Entity\Target;
use App\Entity\User;
use App\Service\ApplicationGlobals;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class UserGroupRoleSubscriber implements EventSubscriber
{

    /**
     * @var \App\Service\ApplicationGlobals
     */
    protected ApplicationGlobals $globals;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected EntityManager $em;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected AuthorizationCheckerInterface $authorizationChecker;
    /**
     * @var bool
     */
    protected bool $entityChange = false;
    /**
     * @var bool
     */
    protected bool $parentGroupUpdated = true;
    /**
     * @var bool
     */
    protected bool $rolesUpdated = true;
    /**
     * @var bool
     */
    protected bool $groupsUpdated = true;

    /**
     * @param \App\Service\ApplicationGlobals                                              $globals
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ApplicationGlobals            $globals,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->globals              = $globals;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return \App\Service\ApplicationGlobals
     */
    protected function getGlobals(): ApplicationGlobals
    {
        return $this->globals;
    }

    /**
     * @return \App\CentralAdmin\KeycloakConnector
     * @throws \Exception
     */
    protected function getKeycloakConnector(): KeycloakConnector
    {
        if (empty($this->keycloakConnector)) {
            $this->keycloakConnector = new KeycloakConnector(
                $this->getGlobals()->getKcUrl(),
                $this->getGlobals()->getKcSecret(),
                $this->getGlobals()->getKcCoreClient(),
                $this->getGlobals()->getKcRealm()
            );
        }
        return $this->keycloakConnector;
    }

    /**
     * @param $entity
     */
    protected function getUpdatedFields($entity): void
    {
        // get updated collections
        foreach ($this->em->getUnitOfWork()->getScheduledCollectionUpdates() as $collectionUpdate) {
            /** @var $collectionUpdate \Doctrine\ORM\PersistentCollection */
            if ($collectionUpdate->getOwner() === $entity) {
                // This entity has an association mapping which contains updates.
                $collectionMapping        = $collectionUpdate->getMapping();
                $this->groupsUpdated      = $collectionMapping['fieldName'] == 'groups';
                $this->rolesUpdated       = $collectionMapping['fieldName'] == 'roles';
                $this->parentGroupUpdated = $collectionMapping['fieldName'] == 'parent';
            }
        }

    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     * @param bool                                           $deletion
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function updateTargets(LifecycleEventArgs $args, bool $deletion = false): void
    {
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();
        if (!is_a($entity, Group::class)
            && !is_a($entity, User::class)
            && !is_a($entity, Permission::class)) {
            return;
        }

        if (is_a($entity, User::class) && !$this->groupsUpdated && !$this->rolesUpdated) {
            // no need to recaclulate target if user's roles and groups still unchanged
            return;
        }
        if (is_a($entity, Group::class) && !$this->parentGroupUpdated) {
            // no need to recaclulate target if group's parent is unchanged
            return;
        }

        // delete targets
        $qbDelete = $this->em->createQueryBuilder();
        $qbDelete->from(Target::class, 't')->delete();

        // recreate all targets
        $qbSelect = $this->em->createQueryBuilder();

        $qbSelect->select([
                              'right.id as rightId',
                              'g.id as groupId',
                              'u.id as userId',
                              'folderTpl.id as folderTplId',
                              'p.id as permissionId'
                          ])->from(Group::class, 'g')
                 ->distinct(true)
                 ->orderBy('g.id')
                 ->innerJoin('g.upUsers', 'u')
                 ->innerJoin('g.roles', 'r')
                 ->innerJoin('r.targetPermissions', 'p')
                 ->innerJoin('p.right', 'right')
                 ->innerJoin('p.userRole', 'up')
                 ->innerJoin('p.folderTpl', 'folderTpl')
                 ->innerJoin('up.legacyUsers', 'legacyUsers')
                 ->where("legacyUsers.id = u.id");

        if (is_a($entity, User::class)) {
            $qbDelete->where(sprintf("t.ownerId = '%s'", $entity->getId()));
            $qbSelect->andWhere(sprintf("u.id = '%s'", $entity->getId()));
        } elseif (is_a($entity, Group::class)) {
            $qbDelete->where(sprintf("t.group = '%s'", $entity->getId()));
            $qbSelect->andWhere(sprintf("g.id = '%s'", $entity->getId()));
        } elseif (is_a($entity, Permission::class)) {
            $qbDelete->where(sprintf("t.permission = '%s'", $entity->getId()));
            $qbSelect->andWhere(sprintf("up.id = '%s'", $entity->getUserRole()->getId()));
            $qbSelect->andWhere(sprintf("r.id = '%s'", $entity->getTargetRole()->getId()));
        }

        // delete relatives targets
        $qbDelete->getQuery()->getResult();
        $this->em->flush();

        if ($deletion) {
            // no need to create targets for a object deletion
            return;
        }
        // get relatives targets
        $rawTargets = $qbSelect->getQuery()->getResult();
        $this->em->flush();

        // create relatives targets
        $targetRepo = $this->em->getRepository(Target::class);
        foreach ($rawTargets as $rawTarget) {
            $group     = $this->em->getRepository(Group::class)->find($rawTarget['groupId']);
            $folderTpl = $this->em->getRepository(FolderTpl::class)->find($rawTarget['folderTplId']);
            $target    = new Target();
            $target->setGroupLabel($group->getLabel());
            $target->setFolderLabel($folderTpl->getLabel());
            $target->setGroup($group);
            $target->setOwnerId($rawTarget['userId']);
            $target->setRight($this->em->getRepository(Right::class)->find($rawTarget['rightId']));
            $target->setFolderTpl($folderTpl);
            if (!empty($this->em->getRepository(Permission::class)->find($rawTarget['permissionId']))) {
                $target->setPermission($this->em->getRepository(Permission::class)->find($rawTarget['permissionId']));
                if (!$targetRepo->exists($target)) {
                    $this->em->persist($target);
                }
            }
        }
        $this->em->flush();
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->getGlobals()->getSecurity()->getUser();
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     */
    public function updateUserChildGroups(LifecycleEventArgs &$args): void
    {
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();
        if (!is_a($entity, Group::class)
            && !is_a($entity, User::class)) {
            return;
        }

        if (is_a($entity, Group::class) && $this->parentGroupUpdated) {
            // ToDo: recalculate all users child groups if it's a group path change
            return;
        }
        if (is_a($entity, User::class) && $this->groupsUpdated) {
            $user = $entity;
            // remove all childGroup
            foreach ($user->getChildGroups() as $childGroup) {
                $user->removeChildGroup($childGroup);
            }
            // add child groups
            foreach ($user->getGroups() as $group) {
                $this->addUserChildGroup($user, $group);
            }
        }

    }

    /**
     * @param \App\Entity\User  $user
     * @param \App\Entity\Group $group
     */
    private function addUserChildGroup(User $user, Group $group): void
    {
        $user->addChildGroup($group);

        foreach ($group->getChildren() as $child) {
            $this->addUserChildGroup($user, $child);
        }
    }

}
