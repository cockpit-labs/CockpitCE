<?php
/*
 * Core
 * Group.php
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

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Attribute\GroupAttribute;
use App\Traits\labelableEntity;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Group
 *
 * @ORM\Table(name="`Groups`")
 * @ORM\Entity()
 *
 */
class Group
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=1000, nullable=true)
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     * @Groups({"Target:Read"})
     */
    public string $path = '';
    /**
     * @var string
     * @ORM\Column(name="idpath", type="string", length=1000, nullable=true)
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     * @Groups({"Target:Read"})
     */
    public string $idPath = '';
    /**
     * @var \App\Entity\Group
     * @ORM\ManyToOne(targetEntity="\App\Entity\Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", nullable=true, referencedColumnName="id")
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     * @Groups({"Target:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $parent;

    /**
     * add a label field
     */
    use labelableEntity;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Role",
     *     inversedBy="groups")
     *
     * @ORM\JoinTable(name="Groups_Roles")
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $roles;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="groups", cascade={"persist"})
     * @ORM\JoinTable(name="Users_Groups")
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $users;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="childGroups", cascade={"persist"})
     * @ORM\JoinTable(name="Users_ChildGroups")
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $upUsers;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Attribute\GroupAttribute",
     *     mappedBy="group",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true)
     *
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     * @Groups({"Target:Read"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    public $attributes;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Target",
     *     mappedBy="group",
     *     fetch="EAGER")
     *
     * @Groups({"Group:Read:View"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     *
     */
    public $targets;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     * @Groups({"Target:Read"})
     */
    private string $id;

    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\Group", mappedBy="parent", cascade={"persist"})
     *
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     * @Groups({"User:Read"})
     * @Groups({"Role:Read"})
     * @Groups({"Target:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $children;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->roles      = new ArrayCollection();
        $this->users      = new ArrayCollection();
        $this->upUsers    = new ArrayCollection();
        $this->targets    = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Attribute\GroupAttribute $attribute
     *
     * @return $this
     */
    public function addAttribute(GroupAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setGroup($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Group $child
     *
     * @return $this
     */
    public function addChild(Group $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
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
            $this->roles[] = $role;
            $role->addGroup($this);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Target $target
     *
     * @return $this
     */
    public function addTarget(Target $target): self
    {
        if (!$this->targets->contains($target)) {
            $this->targets[] = $target;
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function addUpUser(User $user): self
    {
        if (!$this->upUsers->contains($user)) {
            $this->upUsers[] = $user;
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
     * @return GroupAttribute[]
     */
    public function getAttributes(): array
    {
        $this->attributes = $this->attributes ?? new ArrayCollection();
        return $this->attributes->getValues();
    }

    /**
     * @return Group[]
     */
    public function getChildren(): array
    {
        $this->children = $this->children ?? new ArrayCollection();
        return $this->children->getValues();
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
    public function getIdPath(): string
    {
        return $this->idPath;
    }

    /**
     * @param string $idPath
     *
     * @return $this
     */
    public function setIdPath(string $idPath): self
    {
        $this->idPath = $idPath;
        return $this;
    }

    /**
     * @return \App\Entity\Group|null
     */
    public function getParent(): ?Group
    {
        return $this->parent;
    }

    /**
     * @param \App\Entity\Group $parent
     *
     * @return $this
     */
    public function setParent(Group $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
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
     * @param ArrayCollection $roles
     *
     * @return $this
     */
    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        $this->targets = $this->targets ?? new ArrayCollection();
        return $this->targets->getValues();
    }

    /**
     * @return User[]
     */
    public function getUpUsers(): array
    {
        $this->upUsers = $this->upUsers ?? new ArrayCollection();
        return $this->upUsers->getValues();
    }

    /**
     * @param ArrayCollection $users
     *
     * @return $this
     */
    public function setUpUsers(ArrayCollection $users): self
    {
        $this->upUsers = $users;
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
     * @param ArrayCollection $users
     *
     * @return $this
     */
    public function setUsers(ArrayCollection $users): self
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param \App\Entity\Attribute\GroupAttribute $attribute
     *
     * @return $this
     */
    public function removeAttribute(GroupAttribute $attribute): self
    {
        if ($this->attributes->contains($attribute)) {
            $this->attributes->removeElement($attribute);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Group $child
     *
     * @return $this
     */
    public function removeChild(Group $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
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
            $this->roles->removeElement($role);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Target|null $target
     *
     * @return $this
     */
    public function removeTarget(?Target $target = null): self
    {
        if ($target == null) {
            $this->targets = new ArrayCollection();
        } elseif ($this->targets->contains($target)) {
            $this->targets->removeElement($target);
        }
        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function removeUpUser(User $user): self
    {
        if ($this->upUsers->contains($user)) {
            $this->upUsers->removeElement($user);
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
