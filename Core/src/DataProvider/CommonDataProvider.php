<?php
/*
 * Core
 * CommonDataProvider.php
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

namespace App\DataProvider;

use App\CentralAdmin;
use App\CentralAdmin\KeycloakConnector;
use App\Entity\User;
use App\Service\ApplicationGlobals;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Vich\UploaderBundle\Storage\StorageInterface;

class CommonDataProvider
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private ?Request $request;
    /**
     * @var \Twig\Environment;
     */
    private Environment $twig;
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private FilesystemInterface $mediaFS;
    /**
     * @var CentralAdmin\KeycloakConnector
     */
    private KeycloakConnector $keycloakConnector;
    /**
     * @var string
     */
    private string $userId = '';

    /**
     * @var \App\Entity\User
     */
    private User $cockpitUser;

    /**
     * @var UserInterface
     */
    private string|\Stringable|UserInterface $user;
    /**
     * @var string
     */
    private string $appClient = '';
    /**
     * @var NormalizerInterface|null
     */
    private ?NormalizerInterface $normalizer = null;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private KernelInterface $kernel;
    /**
     * @var \Symfony\Component\Mailer\Mailer
     */
    private Mailer|MailerInterface $mailer;
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    private StorageInterface $storage;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    private DenormalizerInterface $denormalizer;
    /**
     * @var string
     */
    private string $keycloakUserId;
    /**
     * @var array
     */
    private array $userRoles = [];

    public function __construct(
        Security               $security,
        NormalizerInterface    $normalizer,
        DenormalizerInterface  $denormalizer,
        EntityManagerInterface $entityManager,
        Environment            $twig,
        ApplicationGlobals     $globals,
        FilesystemInterface    $mediafsFilesystem,
        StorageInterface       $storage,
        RequestStack           $requestStack,
        KernelInterface        $kernel,
        MailerInterface        $mailer,
        ValidatorInterface     $validator,
    ) {
        $this->mediaFS       = $mediafsFilesystem;
        $this->storage       = $storage;
        $this->normalizer    = $normalizer;
        $this->denormalizer  = $denormalizer;
        $this->entityManager = $entityManager;
        $this->twig          = $twig;
        $this->kernel        = $kernel;
        $this->mailer        = $mailer;
        $this->validator     = $validator;
        $this->globals       = $globals;

        if ($security->getToken() != null) {
            // get user
            $this->user      = $security->getToken()->getUser();
            $this->appClient = $this->user->getClient();
        }
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @return string
     */
    public function getAppClient(): string
    {
        return $this->appClient;
    }

    /**
     * @return \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
     */
    public function getDenormalizer(): DenormalizerInterface
    {
        return $this->denormalizer;
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
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /**
     * @return String
     */
    public function getKeycloakClientAuthUrl(): string
    {
        return $this->getGlobals()->getKcAuthUrl();
    }

    /**
     * @return CentralAdmin\KeycloakConnector
     */
    public function getKeycloakConnector(): KeycloakConnector
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
     * @return String
     */
    public function getKeycloakRealm(): string
    {
        return $this->getGlobals()->getKcRealm();
    }

    /**
     * @return String
     */
    public function getKeycloakUrl(): string
    {
        return $this->getGlobals()->getKcUrl();
    }

    /**
     * @return \Symfony\Component\Mailer\Mailer
     */
    public function getMailer(): Mailer
    {
        return $this->mailer;
    }

    /**
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getMediaFS(): FilesystemInterface
    {
        return $this->mediaFS;
    }

    /**
     * @return \Symfony\Component\Serializer\Normalizer\NormalizerInterface|null
     */
    public function getNormalizer(): ?NormalizerInterface
    {
        return $this->normalizer;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @return \Vich\UploaderBundle\Storage\StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return \Twig\Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return object|string
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * return cockît user Id
     *
     * @return string
     */
    public function getUserId(): string
    {
        if (empty($this->userId)) {
            $u = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $this->getUser()->getUsername()]);
            $this->setCockpitUser($u);
            $this->userId = $u->getId();

        }
        return $this->userId;
    }

    /**
     * @return \App\Entity\User
     */
    public function getCockpitUser(): User
    {
        if (empty($this->cockpitUser)) {
            $u                 = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $this->getUser()->getUsername()]);
            $this->cockpitUser = $u;
            $this->setUserId($u->getId());
        }
        return $this->cockpitUser;
    }

    /**
     * return Keycloak user Id
     *
     * @return string
     */
    public function getKeycloakUserId(): string
    {
        if (empty($this->keycloakUserId)) {
            $this->keycloakUserId = $this->getUser()->getId();
        }
        return $this->keycloakUserId;
    }

    /**
     * @return array
     */
    public function getUserRoles(): array
    {
        if (empty($this->userRoles)) {
            $u = $this->getEntityManager()->getRepository(User::class)->find($this->getUserId());

            foreach ($u->getEffectiveRoles() as $userRole) {
                $this->userRoles[] = $userRole;
            }
        }
        return $this->userRoles;
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param string $userId
     *
     * @return $this
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param \App\Entity\User $cockpitUser
     *
     * @return $this
     */
    public function setCockpitUser(User $cockpitUser): self
    {
        $this->cockpitUser = $cockpitUser;
        return $this;
    }

}
