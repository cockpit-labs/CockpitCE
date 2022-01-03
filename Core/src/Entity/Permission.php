<?php
/*
 * Core
 * Permission.php
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
use App\Entity\Folder\FolderTpl;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Permission
 *
 * @ORM\Table(
 *     name="Permissions",
 * )
 * @UniqueEntity(
 *      fields={"right","userRole", "targetRole", "folderTpl"},
 *      message="permission already exists in database."
 * )
 *
 * @ORM\Entity
 *
 */
class Permission
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({"Permission:Read"})
     * @Groups({"Permission:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     */
    public $id;

    /**
     * @var \App\Entity\Right
     * @Assert\NotNull()
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne (targetEntity="Right")
     * @ORM\JoinColumn(name="`right`", nullable=false, referencedColumnName="id")
     *
     * @Groups({"Permission:Read"})
     * @Groups({"Permission:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     */
    private $right;

    /**
     * @var \App\Entity\Role|null
     * @Assert\NotNull()
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="target_role",
     *     onDelete="CASCADE",
     *     referencedColumnName="id")
     * })
     * @Groups({"Permission:Read"})
     * @Groups({"Permission:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $targetRole;

    /**
     * @var \App\Entity\Role|null
     * @Assert\NotNull()
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_role",
     *     onDelete="CASCADE",
     *     referencedColumnName="id")
     * })
     * @Groups({"Permission:Read"})
     * @Groups({"Permission:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $userRole;

    /**
     * @var FolderTpl
     * @Assert\NotNull()
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(
     *     targetEntity="\App\Entity\Folder\FolderTpl",
     *     inversedBy="permissions"
     * )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderTpl_id",
     *     onDelete="CASCADE",
     *     referencedColumnName="id")
     * })
     *
     * @Groups({"FolderTpl:Update"})
     * @Groups({"Permission:Read"})
     * @Groups({"Permission:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $folderTpl;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="\App\Entity\Target",
     *     mappedBy="permission",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     *     )
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $targets;

    public function __construct()
    {
        $this->targets = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Right
     */
    public function getRight(): Right
    {
        return $this->right;
    }

    /**
     * @return \App\Entity\Role|null
     */
    public function getTargetRole(): ?Role
    {
        return $this->targetRole;
    }

    /**
     * @param \App\Entity\Role|null $targetRole
     *
     * @return $this
     */
    public function setTargetRole(?Role $targetRole): self
    {
        $this->targetRole = $targetRole;

        return $this;
    }

    /**
     * @return \App\Entity\Role|null
     */
    public function getUserRole(): ?Role
    {
        return $this->userRole;
    }

    /**
     * @param \App\Entity\Role|null $userRole
     *
     * @return $this
     */
    public function setUserRole(?Role $userRole): self
    {
        $this->userRole = $userRole;

        return $this;
    }

    /**
     * @return \App\Entity\Folder\FolderTpl|null
     */
    public function getFolderTpl(): ?FolderTpl
    {
        return $this->folderTpl;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl|null $folderTpl
     *
     * @return $this
     */
    public function setFolderTpl(?FolderTpl $folderTpl): self
    {
        $this->folderTpl = $folderTpl;

        return $this;
    }

    /**
     * @param \App\Entity\Right $right
     *
     * @return $this
     */
    public function setRight(Right $right): self
    {
        $this->right = $right;
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
            $this->targets->add($target);
            $target->setPermission($this);
        }

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
     * @param ArrayCollection
     *
     * @return $this
     */
    public function setTargets(ArrayCollection $targets): self
    {
        $this->targets = $targets ?? new ArrayCollection();
        return $this;
    }

    /**
     * @param \App\Entity\Target $target
     *
     * @return $this
     */
    public function removeTarget(Target $target): self
    {
        if ($this->targets->contains($target)) {
            $this->targets->removeElement($target);
        }

        return $this;
    }
}
