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


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\resourceableEntity;
use App\Traits\traceableEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Role
 *
 * @ORM\Table(name="Users")
 * @ORM\Entity
 * @ApiFilter(DateFilter::class, properties={"createdAt","updatedAt"})
 * @ApiFilter(SearchFilter::class, properties={"username": "partial", "email": "partial", "enabled": "exact"})
 */
class User
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * add group (Timestamp and Blame) for TimestampableEntity and BlameableEntity
     */
    use traceableEntity;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=100, nullable=false, unique=true)
     * @Groups({"User:Read"})
     * @Groups({"Group:Read"})
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Create"})
     * @Groups({"Role:Read"})
     *
     */
    public string $username = '';

    /**
     * @var string
     * @Groups({"User:Read"})
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     *
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
     */
    public string $email = '';

    /**
     * @var bool
     * @ORM\Column(name="email_verified", type="boolean", nullable=false, options={"default" : 0})
     *
     * @Groups({"Admin:Read"})
     * @Groups({"Superuser:Update"})
     */
    public bool $emailVerified = false;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default" : 1})
     *
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     */
    public bool $enabled = true;

    /**
     * @var string
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"User:Read"})
     *
     * @Groups({"Group:Read"})
     * @Groups({"Role:Read"})
     *
     * @ORM\Column(type="string")
     */
    public string $firstname = '';

    /**
     * @var string
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"User:Read"})
     *
     * @Groups({"Group:Read"})
     * @Groups({"Role:Read"})
     *
     * @ORM\Column(type="string")
     */
    public string $lastname = '';

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Group", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="Users_Groups")
     *
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Group", inversedBy="upUsers", cascade={"persist"})
     * @ORM\JoinTable(name="Users_ChildGroups")
     *
     * @Groups({"Admin:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $childGroups;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="Users_Roles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     *
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $roles;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Role", inversedBy="legacyUsers")
     * @ORM\JoinTable(name="Users_EffectiveRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     *
     * @Groups({"Admin:Read"})
     * @Groups({"Admin:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $effectiveRoles;


    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * ORM\GeneratedValue(strategy="UUID")
     * @Groups({"Group:Read"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     * @Groups({"Admin:Read"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Update"})
     * @Groups({"Questionnaire:Read"})
     */
    private string $id = '';

    public function __construct()
    {
        $this->groups         = new ArrayCollection();
        $this->childGroups    = new ArrayCollection();
        $this->roles          = new ArrayCollection();
        $this->effectiveRoles = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @return $this
     */
    public function addChildGroup(Group $group): self
    {
        if (!$this->childGroups->contains($group)) {
            $this->childGroups->add($group);
            $group->addUpUser($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Role $effectiveRole
     *
     * @return $this
     */
    public function addEffectiveRole(Role $effectiveRole): self
    {
        if (!$this->effectiveRoles->contains($effectiveRole)) {
            $this->effectiveRoles->add($effectiveRole);
            $effectiveRole->addLegacyUser($this);
        }
        return $this;
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
            $group->addUser($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Role $role
     *
     * @return $this
     */
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }
        return $this;
    }

    /**
     * @return User[]
     */
    public function getChildGroups(): array
    {
        $this->childGroups = $this->childGroups ?? new ArrayCollection();
        return $this->childGroups->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $childGroups
     *
     * @return $this
     */
    public function setChildGroups(ArrayCollection $childGroups): User
    {
        $this->childGroups = $childGroups;
        return $this;
    }

    /**
     * @return Role[]
     */
    public function getEffectiveRoles(): array
    {
        $this->effectiveRoles = $this->effectiveRoles ?? new ArrayCollection();
        return $this->effectiveRoles->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $effectiveRoles
     *
     * @return $this
     */
    public function setEffectiveRoles(ArrayCollection $effectiveRoles): self
    {
        $this->effectiveRoles = $effectiveRoles;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
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
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): array
    {
        $this->roles = $this->roles ?? new ArrayCollection();
        return $this->roles->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $roles
     *
     * @return $this
     */
    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return Group
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    /**
     * @param bool $emailVerified
     *
     * @return $this
     */
    public function setEmailVerified(bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $tis
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @return $this
     */
    public function removeChildGroup(?Group $group = null): self
    {
        if (empty($group)) {
            $this->childGroups = new ArrayCollection();
        } elseif ($this->childGroups->contains($group)) {
            $this->childGroups->removeElement($group);
            $group->removeUser($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Role|null $effectiveRole
     *
     * @return $this
     */
    public function removeEffectiveRole(?Role $effectiveRole = null): self
    {
        if (empty($effectiveRole)) {
            // remove all
            foreach ($this->effectiveRoles as $effectiveRole) {
                $this->effectiveRoles->removeElement($effectiveRole);
                $effectiveRole->removeUser($this);
            }
        } elseif ($this->effectiveRoles->contains($effectiveRole)) {
            $this->effectiveRoles->removeElement($effectiveRole);
            $effectiveRole->removeUser($this);
        }
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
            $group->removeUser($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Role $role
     *
     * @return $this
     */
    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $role->removeUser($this);
            $this->roles->removeElement($role);
        }
        return $this;
    }
}
