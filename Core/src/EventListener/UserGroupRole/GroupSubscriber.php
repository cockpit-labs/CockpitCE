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

use App\Entity\Group;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GroupSubscriber extends UserGroupRoleSubscriber
{
    /**
     * @param \App\Entity\Group $group
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function addKeycloakGroup(Group &$group): void
    {
        $kc             = $this->getKeycloakConnector();
        $kgroup['name'] = $group->getLabel();
        $parentId       = null;
        if ($group->getParent()) {
            $parentId = $group->getParent()->getId();
        }
        $kgroup['parent'] = $parentId;

        if ($id = $kc->addGroup($kgroup)) {
            $kgroup = $kc->getGroup($id);
            $group->setId($id);
            $group->setPath($kgroup['path']);

            foreach ($group->getRoles() as $role) {
                $kc->setGroupRole($id, ['id' => $role->getId(), 'name' => $role->getName()]);
            }
        } else {
            if ($kc->getLastError() == 409) {
                throw new ConflictHttpException('Group exists with same label at this level');
            }
            throw new UnprocessableEntityHttpException();
        }
    }

    /**
     * @param \App\Entity\Group|null $group
     *
     * @return string
     */
    private function getGroupIdPath(?Group $group): string
    {
        $idPath = '/' . $group->getId();
        if (!empty($group->getParent())) {
            $idPath = $this->getGroupIdPath($group->getParent()) . $idPath;
        }
        return $idPath;
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function removeKeycloakGroup(Group $group): void
    {
        $kc = $this->getKeycloakConnector();

        $keycloakGroup = $kc->getGroup($group->getId());
        if (empty($keycloakGroup)) {
            throw new NotFoundHttpException();
        }

        if (!$kc->deleteRole($group->getId())) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param \App\Entity\Group $group
     */
    private function updateGroup(Group $group): void
    {
        $group->setIdPath($this->getGroupIdPath($group));
        $this->em->persist($group);
        $this->em->flush();

    }

    /**
     * @param \App\Entity\Group $group
     *
     * @throws \Exception
     */
    private function updateKeycloakGroup(Group &$group): void
    {
        if (!$this->entityChange) {
            return;
        }

        $kc     = $this->getKeycloakConnector();
        $kgroup = $kc->getGroup($group->getId());
        if (empty($group)) {
            throw new NotFoundHttpException();
        }
        $newParentGroupId = empty($group->getParent()) ? null : $group->getParent()->getId();
        if ($newParentGroupId === $group->getId()) {
            throw new NotAcceptableHttpException("I can't be my parent");
        }

        $oldParentGroupId = $kgroup['parent'] ?? null;

        if ($newParentGroupId != $oldParentGroupId) {
            // group is moving in the tree
            $kc->updateGroupSetParent($group->getId(), $newParentGroupId);
        }

        // update group
        $kgroup['name'] = $group->getLabel();

        if ($kc->updateGroup($group->getId(), $kgroup)) {
            $kgroup = $kc->getGroup($group->getId());
            $group->setPath($kgroup['path']);
            // update roles if needed
            if ($this->rolesUpdated) {
                $roles = $kc->getGroupRoles($group->getId());
                foreach ($roles as $role) {
                    $kc->deleteGroupRole($group->getId(), $role);
                }

                foreach ($group->getRoles() as $role) {
                    $kc->setGroupRole($group->getId(),
                                      [
                                          'id'   => $role->getId(),
                                          'name' => $role->getName()
                                      ]);
                }
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
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }
        $this->updateGroup($args->getObject());
        $this->updateTargets($args);

    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $this->updateTargets($args, true);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $this->getUpdatedFields($args->getObject());
        $this->updateGroup($args->getObject());
        $this->updateTargets($args, true);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }

        $entity   = $args->getObject();
        $this->em = $args->getObjectManager();

        $this->addKeycloakGroup($entity);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }
        $this->em = $args->getObjectManager();
        $entity   = $args->getObject();

        $this->getUpdatedFields($entity);
        // sync keycloak group
        $this->removeKeycloakGroup($entity);
    }

    /**
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) !== Group::class) {
            return;
        }
        $this->em           = $args->getObjectManager();
        $entity             = $args->getObject();
        $this->entityChange = !empty($args->getEntityChangeSet());

        $this->getUpdatedFields($entity);
        // sync keycloak group
        $this->updateKeycloakGroup($entity);
    }

}
