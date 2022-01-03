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

use App\CentralAdmin\KeycloakConnector;
use App\Entity\Role;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RoleSubscriber extends UserGroupRoleSubscriber
{
    /**
     * @param \App\Entity\Role $role
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function addKeycloakRole(Role &$role): void
    {
        if ($role->isSystem() && !$this->authorizationChecker->isGranted(KeycloakConnector::toSymfonyRole('Superuser'))) {
            throw new AccessDeniedHttpException();
        }

        $kc = $this->getKeycloakConnector();
        if (!empty($kc->getRoleById($role->getId()))) {
            if ($this->authorizationChecker->isGranted(KeycloakConnector::toSymfonyRole('Superuser'))) {
                return;
            }
            throw new ConflictHttpException();
        }

        if (!$kc->addRole($role->getName(), $role->getDescription())) {
            throw new UnprocessableEntityHttpException();
        }

        $kcrole = $kc->getRoleByName($role->getName());
        $role->setId($kcrole['id']);
    }

    /**
     * @param \App\Entity\Role $role
     */
    private function checkRoleIsNoSystem(Role $role): void
    {
        if ($role->isSystem()) {
            throw new AccessDeniedHttpException('This is a system role');
        }
    }

    /**
     * @param \App\Entity\Role $role
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function removeKeycloakRole(Role $role): void
    {
        if ($role->isSystem()) {
            return;
        }
        $kc = $this->getKeycloakConnector();

        $keycloakRole = $kc->getRoleById($role->getId());
        if (empty($keycloakRole)) {
            throw new NotFoundHttpException();
        }
        $keycloakRole['name'] = $role->getName();

        if (!$kc->deleteRole($keycloakRole['name'])) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param \App\Entity\Role $role
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function updateKeycloakRole(Role &$role): void
    {
        if (!$this->entityChange) {
            return;
        }

        if ($role->isSystem() && !$this->authorizationChecker->isGranted('ROLE_Superuser')) {
            throw new AccessDeniedHttpException();
        }

        $kc           = $this->getKeycloakConnector();
        $keycloakRole = $kc->getRoleById($role->getId());
        if (empty($keycloakRole)) {
            throw new NotFoundHttpException();
        }

        if ($keycloakRole['attributes']['system'][0] ?? 0 || $role->isSystem()) {
            throw new AccessDeniedHttpException();
        }

        $existingRole = $this->em->getRepository(Role::class)->findOneBy(["name" => $role->getName()]);
        if ($existingRole && $existingRole->getId() != $role->getId()) {
            throw new ConflictHttpException();
        }
        $keycloakRole['name']        = $role->getName();
        $keycloakRole['description'] = $role->getDescription();
        if (!$kc->updateRole($role->getId(), $keycloakRole)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preRemove,
            Events::postRemove,
            Events::preUpdate,
        ];
    }


    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Role::class) {
            return;
        }
        $this->updateTargets($args, true);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Role::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();

        // sync keycloak role
        $this->addKeycloakRole($entity);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Role::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();

        $this->checkRoleIsNoSystem($entity);
        // sync keycloak role
        $this->removeKeycloakRole($entity);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Role::class) {
            return;
        }
        $this->em           = $args->getObjectManager();
        $entity             = $args->getObject();
        $this->entityChange = !empty($args->getEntityChangeSet());

        // sync keycloak role
        $this->checkRoleIsNoSystem($entity);
        $this->updateKeycloakRole($entity);

    }

}
