<?php
/*
 * Core
 * Role.php
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


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\descriptionableEntity;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Role
 *
 * @ORM\Table(name="Roles")
 * @ORM\Entity
 * @UniqueEntity("name")
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "exact"
 *      })
 * @ApiFilter(BooleanFilter::class, properties={
 *     "system": "exact"
 *      })
 *
 */
class Role
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * add a description field
     */
    use descriptionableEntity;

    /**
     * @var bool
     * @ORM\Column(name="system_role", type="boolean", nullable=false, options={"default" : 0})
     *
     * @Groups({"Role:Read"})
     * @Groups({"User:Read"})
     * @Groups({"Group:Read"})
     */
    public bool $system = false;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     * @Groups({"Group:Read"})
     * @Groups({"Role:Update"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     *
     */
    public string $name = '';

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="\App\Entity\Group",
     *     mappedBy="roles")
     * @ORM\JoinTable(name="Groups_Roles")
     *
     * @Groups({"Role:Read"})
     * @Groups({"Role:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $groups;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Permission",
     *     mappedBy="targetRole",
     *     cascade={"persist", "remove"})
     */
    public $targetPermissions;

    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Permission",
     *     mappedBy="userRole",
     *     cascade={"persist", "remove"})
     */
    public $userPermissions;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="effectiveRoles")
     * @ORM\JoinTable(name="Users_EffectiveRoles")
     *
     * @Groups({"Role:Read"})
     * @Groups({"Group:Read"})
     * @Groups({"Role:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $legacyUsers;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="roles")
     * @ORM\JoinTable(name="Users_Roles")
     *
     * @Groups({"Role:Read"})
     * @Groups({"Group:Read"})
     * @Groups({"Role:Update"})
     *
     * ApiProperty(readableLink=false, readable=true)
     */
    public $users;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @Groups({"Role:Read"})
     */
    private string $id = '';

    public function __construct()
    {
        $this->groups            = new ArrayCollection();
        $this->targetPermissions = new ArrayCollection();
        $this->userPermissions   = new ArrayCollection();
        $this->users             = new ArrayCollection();
        $this->legacyUsers       = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @return $this
     */
    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function addLegacyUser(User $user): self
    {
        if (!$this->legacyUsers->contains($user)) {
            $this->legacyUsers[] = $user;
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }
        return $this;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        $this->groups = $this->groups ?? new ArrayCollection();
        return $this->groups->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $groups
     *
     * @return $this
     */
    public function setGroups(ArrayCollection $groups): self
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User[]
     */
    public function getLegacyUsers(): array
    {
        $this->legacyUsers = $this->legacyUsers ?? new ArrayCollection();
        return $this->legacyUsers->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $legacyUsers
     *
     * @return $this
     */
    public function setLegacyUsers(ArrayCollection $legacyUsers): self
    {
        $this->legacyUsers = $legacyUsers;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        $this->users = $this->users ?? new ArrayCollection();
        return $this->users->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $users
     *
     * @return $this
     */
    public function setUsers(ArrayCollection $users): self
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->system;
    }

    /**
     * @param bool $system
     *
     * @return $this
     */
    public function setSystem(bool $system): self
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @return $this
     */
    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function removeLegacyUser(User $user): self
    {
        if ($this->legacyUsers->contains($user)) {
            $this->legacyUsers->removeElement($user);
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
        return $this;
    }

}
