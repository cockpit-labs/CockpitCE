<?php


/*
 * Core
 * UserSubscriber.php
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

use App\Entity\Config;
use App\Entity\Folder\Folder;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserSubscriber extends UserGroupRoleSubscriber
{
    /**
     * @param \App\Entity\User $user
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function addKeycloakUser(User $user): void
    {
        $kc = $this->getKeycloakConnector();
        if (!empty($kc->getUser($user->getUsername())) && $user->getUsername() !== 'superuser') {
            throw new ConflictHttpException();
        }
        $kuser['username']  = $user->getUsername();
        $kuser['email']     = $user->getEmail();
        $kuser['firstName'] = $user->getFirstname();
        $kuser['lastName']  = $user->getLastname();

        // no required actions for user created by superuser
        if ($this->getUser()->getUsername() === 'superuser') {
            $kuser['emailVerified'] = true;
        } else {
            $kuser['requiredActions'] = [
                'UPDATE_PASSWORD',
                'VERIFY_EMAIL',
                'UPDATE_PROFILE',
                'update_user_locale'
            ];
        }
        $kuser['enabled'] = true;

        if ($user->getUsername() === 'superuser' || $kc->addUser($kuser)) {
            $kuser = $kc->getUser($kuser['username']);
            $user->setId($kuser['id']);

            foreach ($user->getRoles() as $role) {
                $kc->setUserRole($kuser['id'], ['id' => $role->getId(), 'name' => $role->getName()]);
            }

            foreach ($user->getGroups() as $group) {
                $kc->setUserGroup($kuser['id'], ['id' => $group->getId()]);
            }

            if ($this->getGlobals()->welcomeUser()) {
                $kc->welcomeUser($kuser['id'], $this->getGlobals()->getBaseUrl() . '/view', 'view');
            }

        } else {
            throw new UnprocessableEntityHttpException();
        }
    }

    /**
     * @param \App\Entity\User $user
     */
    private function checkUserIsDataFree(User $user): void
    {
        // Check if user has any data:
        // - folderTpl
        // - folder
        foreach ([
                     Folder::class,
                 ] as $class) {
            $repo = $this->em->getRepository($class);
            $data = $repo->findBy(['createdBy' => $user->getUsername()]);
            if (!empty($data)) {
                $p              = explode("\\", $class);
                $shortClassname = end($p);
                throw new AccessDeniedHttpException("There is $shortClassname(s) linked to this user");
            }
        }
    }

    /**
     * @param \App\Entity\User $user
     */
    private function ensureUserDoNotExists(User $user): void
    {
        $userRepo = $this->em->getRepository(User::class);
        $userId   = $user->getId() ?: 'noid';
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->neq('id', $userId))
                 ->andWhere(Criteria::expr()->eq('username', $user->getUsername()));
        $users = $userRepo->matching($criteria);
        if ($users->count()) {
            throw new ConflictHttpException('User exists with same username');
        }
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->neq('id', $userId))
                 ->andWhere(Criteria::expr()->eq('email', $user->getEmail()));
        $users = $userRepo->matching($criteria);
        if ($users->count()) {
            throw new ConflictHttpException('User exists with same email');
        }
    }

    /**
     * @param \App\Entity\User $user
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function removeKeycloakUser(User $user): void
    {
        $kc = $this->getKeycloakConnector();

        $keycloakUser = $kc->getUser($user->getId());
        if (empty($keycloakUser)) {
            throw new NotFoundHttpException();
        }

        if (!$kc->deleteUser($user->getId())) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param \App\Entity\User $user
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function updateKeycloakUser(User $user): void
    {
        $kc    = $this->getKeycloakConnector();
        $kuser = $kc->getUser($user->getId());
        if (empty($kuser)) {
            throw new NotFoundHttpException();
        }
        $kuser['username']  = $user->getUsername();
        $kuser['email']     = $user->getEmail();
        $kuser['firstName'] = $user->getFirstname();
        $kuser['lastName']  = $user->getLastname();
        $kuser['enabled'] = $user->isEnabled();
        $user->setEmailVerified($kuser['emailVerified']);

        if ($kc->updateUser($user->getId(), $kuser)) {
            // update roles if needed
            if ($this->rolesUpdated) {
                $roles = $kc->getUserRoles($user->getId());
                foreach ($roles as $role) {
                    $kc->deleteUserRole($user->getId(), $role);
                }

                foreach ($user->getRoles() as $role) {
                    $kc->setUserRole($user->getId(),
                                     [
                                         'id'   => $role->getId(),
                                         'name' => $role->getName()
                                     ]);
                }
            }
            // update groups if needed
            if ($this->groupsUpdated) {
                $groups = $kc->getUserGroups($user->getId());
                foreach ($groups as $group) {
                    $kc->deleteUserGroup($user->getId(), $group);
                }

                foreach ($user->getGroups() as $group) {
                    $kc->setUserGroup($user->getId(),
                                      [
                                          'id' => $group->getId(),
                                      ]);
                }
            }
        }
    }

    private function updateUserEffectiveRoles(User &$user): void
    {
        $kc = $this->getKeycloakConnector();
        // delete effective roles
        $user->removeEffectiveRole();

        $effectiveRoles = $kc->getUserEffectiveRoles($user->getId());
        if (!empty($effectiveRoles)) {
            $keycloakRoleIds = [];
            foreach ($effectiveRoles as $effectiveRole) {
                $keycloakRoleIds[] = $effectiveRole['id'];
            }
            $roles = $this->em->getRepository(Role::class)->findBy(['id' => $keycloakRoleIds]);
            foreach ($roles as $role) {
                $user->addEffectiveRole($role);
            }
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preRemove,
            Events::postRemove,
            Events::preUpdate,
            Events::postUpdate,
        ];
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $entity = $args->getObject();
        $this->getUpdatedFields($args->getObject());
        $this->updateTargets($args);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $this->updateTargets($args, true);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();
        $this->getUpdatedFields($args->getObject());
        $this->updateTargets($args);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();

        $this->ensureUserDoNotExists($entity);
        $this->addKeycloakUser($entity);
        $this->updateUserChildGroups($args);
        $this->updateUserEffectiveRoles($entity);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();
        // Check if user does not have any data
        $this->getUpdatedFields($entity);
        $this->checkUserIsDataFree($entity);
        // sync keycloak user
        $this->removeKeycloakuser($entity);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== User::class) {
            return;
        }
        $this->em           = $args->getObjectManager();
        $entity             = $args->getObject();
        $this->entityChange = !empty($args->getEntityChangeSet());
        $this->getUpdatedFields($entity);
        $this->updateKeycloakUser($entity);
        $this->updateUserChildGroups($args);
        $this->updateUserEffectiveRoles($entity);
        $this->em->persist($entity);
    }
}
