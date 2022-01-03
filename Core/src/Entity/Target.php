<?php
/*
 * Core
 * Target.php
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
use App\Entity\Folder\Folder;
use App\Entity\Folder\FolderTpl;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Target
 * @ORM\Table(name="Targets")
 * @ORM\Entity(repositoryClass="App\Repository\TargetRepository")
 *
 * @UniqueEntity(
 *      fields={"group", "permission", "ownerId", "right", "folderTpl"},
 *      message="target already exists in database."
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "right": "exact",
 *     "folderTpl.id": "exact",
 *     "folder.id": "exact"
 * })
 * @ApiFilter(BooleanFilter::class, properties={
 *     "folderTpls.calendars.valid": "exact"
 *      })
 *
 */
class Target
{

    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * Unique identifier for the object.
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string | null
     *
     * @Groups({"Target:Read"})
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=36)
     *
     * @Groups({"Target:Read"})
     */
    public $ownerId;

    /**
     * @var string
     *
     * @Groups({"Target:Read"})
     * @Groups({"Group:Read:View"})
     *
     * @ORM\Column(type="string")
     */
    private $groupLabel;

    /**
     * @var string
     *
     * @Groups({"Target:Read"})
     * @Groups({"Group:Read:View"})
     *
     * @ORM\Column(type="string")
     */
    private $folderLabel;

    /**
     * @var \App\Entity\Right
     * @ORM\ManyToOne(targetEntity="\App\Entity\Right")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="`right`", referencedColumnName="id")
     * })
     * @Groups({"Target:Read"})
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $right;

    /**
     * @var \App\Entity\Folder\FolderTpl
     * @ORM\ManyToOne(targetEntity="\App\Entity\Folder\FolderTpl")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderTpl_id", referencedColumnName="id")
     * })
     * @Groups({"Target:Read"})
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $folderTpl;

    /**
     * @var ArrayCollection
     *
     * @Groups({"Target:Read"})
     * @Groups({"Group:Read:View"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     *
     */
    private $folders;

    /**
     * @var \App\Entity\Group
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Group")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     * })
     * @Groups({"Target:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $group;

    /**
     * @var \App\Entity\Permission
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Permission")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="permission_id", referencedColumnName="id")
     * })
     * @Groups({"Target:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $permission;

    public function __construct()
    {
        $this->folders = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Folder $folder
     *
     * @return $this
     */
    public function addFolder(Folder $folder): self
    {
        $this->folders = $this->folders ?? new ArrayCollection();
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFolderLabel(): ?string
    {
        return $this->folderLabel;
    }

    /**
     * @param string|null $folderLabel
     *
     * @return $this
     */
    public function setFolderLabel(?string $folderLabel): self
    {
        $this->folderLabel = $folderLabel;

        return $this;
    }

    /**
     * @return \App\Entity\Folder\FolderTpl
     */
    public function getFolderTpl(): FolderTpl
    {
        return $this->folderTpl;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl $folderTpl
     *
     * @return $this
     */
    public function setFolderTpl(FolderTpl $folderTpl): self
    {
        $this->folderTpl = $folderTpl;
        return $this;
    }

    /**
     * @return Folder[]
     */
    public function getFolders(): array
    {
        $this->folders = $this->folders ?? new ArrayCollection();
        return $this->folders->getValues();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $folders
     *
     * @return $this
     */
    public function setFolders(ArrayCollection $folders): self
    {
        $this->folders = $folders;
        return $this;
    }

    /**
     * @return \App\Entity\Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param \App\Entity\Group $group
     *
     * @return $this
     */
    public function setGroup(Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroupLabel(): ?string
    {
        return $this->groupLabel;
    }

    /**
     * @param string|null $groupLabel
     *
     * @return $this
     */
    public function setGroupLabel(?string $groupLabel): self
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return $this
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    /**
     * @param string $ownerId
     *
     * @return $this
     */
    public function setOwnerId(string $ownerId): self
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    /**
     * @return \App\Entity\Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * @param \App\Entity\Permission $permission
     *
     * @return $this
     */
    public function setPermission(Permission $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return \App\Entity\Right
     */
    public function getRight(): \App\Entity\Right
    {
        return $this->right;
    }

    /**
     * @param \App\Entity\Right $right
     *
     * @return $this
     */
    public function setRight(\App\Entity\Right $right): self
    {
        $this->right = $right;
        return $this;
    }

    /**
     * @param \App\Entity\Folder $folder
     *
     * @return $this
     */
    public function removeFolder(Folder $folder): self
    {
        if ($this->getFolders()->contains($folder)) {
            $this->getFolders()->removeElement($folder);
        }
        return $this;
    }
}
