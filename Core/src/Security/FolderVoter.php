<?php
/*
 * Core
 * FolderVoter.php
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

use App\Entity\Folder\Folder;
use App\Entity\Group;
use App\Entity\Right;
use App\Entity\Target;
use App\Service\ApplicationGlobals;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FolderVoter extends Voter
{
    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private ?Request $request;
    /**
     * @var mixed
     */
    private Folder $folder;
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    private TokenInterface $token;

    /**
     * FolderVoter constructor.
     *
     * @param \App\Service\ApplicationGlobals                $globals
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Doctrine\ORM\EntityManagerInterface           $entityManager
     */
    public function __construct(
        ApplicationGlobals $globals,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    ) {
        $this->globals = $globals;
        $this->globals->setEntityManager($entityManager);
        $this->request          = $requestStack->getCurrentRequest();
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        // only vote on `Folder` objects
        if (!$subject instanceof Folder) {
            return false;
        }

        return true;
    }

    /**
     * @param string                                                               $right
     * @param mixed                                                                $folder
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $rights      = [];
        $this->token = $token;
        $sUser       = $this->token->getUser();

        $this->folder = $subject;

        if (!$sUser instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($attribute === 'OWNER') {
            return $this->isOwner();
        }

        return $this->checkPermission([$attribute]);
    }

    /**
     * @return bool
     */
    private function isDeletable(): bool
    {
        return $this->folder->getState() === 'DRAFT';
    }

    /**
     * @return bool
     */
    private function isOwner(): bool
    {
        return ($this->folder->getCreatedBy() === $this->token->getUser()->getUsername());
    }

    /**
     * @param array $rights
     *
     * @return bool
     */
    private function checkPermission(array $rights): bool
    {
        $sUser = $this->token->getUser();

        // get user
        $user = $this->globals->getEntityManager()->getRepository(\App\Entity\User::class)->findOneBy(['username' => $sUser->getUsername()]);

        // get group folder
        $groupId = $this->folder->getappliedTo();

        $targetRepo = $this->globals->getEntityManager()->getRepository(Target::class);

        // check if user has right on folder
        foreach ($rights as $right) {
            $t = $targetRepo->findOneBy([
                                            'group'   => $this->globals->getEntityManager()->getRepository(Group::class)->find($groupId),
                                            'ownerId' => $user->getId(),
                                            'right'   => $this->globals->getEntityManager()->getRepository(Right::class)->find($right)
                                        ]);
            if (!empty($t)) {
                return true;
            }
        }
        return false;

    }
}
