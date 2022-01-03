<?php
/*
 * Core
 * FolderBase.php
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

namespace App\Entity\Folder;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Calendar;
use App\Entity\Permission;
use App\Traits\descriptionableEntity;
use App\Traits\labelableEntity;
use App\Traits\stateableEntity;
use App\Traits\traceableEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * BaseFolder
 *
 * @ORM\Table(
 *     name="Folders",
 *     indexes={
 *      @ORM\Index(name="Folderdeleted_idx", columns={"deleted_at"})
 *     }
 * )
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *     "Template"="FolderTpl",
 *     "Instance"="Folder"
 * })
 *
 * @Gedmo\Loggable
 */
abstract class FolderBase implements Translatable
{
    /**
     * add a state field
     */
    use stateableEntity;

    /**
     * add group (Timestamp and Blame) for TimestampableEntity and BlameableEntity
     */
    use traceableEntity;

    /**
     * Hook blameable behavior
     * updates createdBy, updatedBy fields
     */
    use BlameableEntity;

    /**
     * Hook timestampable behavior
     * updates writedAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * add a label field
     */
    use labelableEntity;

    /**
     * add a description field
     */
    use descriptionableEntity;


    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     * @ApiProperty(iri="https://schema.org/identifier", identifier=true)
     *
     * @Groups({"Submit"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Target:Read"})
     * @Groups({"Score"})
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"FolderTpl:Expectation"})
     * @Groups({"FolderTpl:Periods"})
     */
    public $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="periodStart", type="datetime", nullable=true)
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Folder:Read"})
     * @Groups({"Group:Read:View"})
     */
    public $periodStart = null;

    /**
     * @var \DateTime
     * @ORM\Column(name="periodEnd", type="datetime", nullable=true)
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Folder:Read"})
     * @Groups({"Group:Read:View"})
     */
    public $periodEnd = null;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\App\Entity\Calendar",
     *     inversedBy="folders",
     *     cascade={"persist"})
     * @ORM\JoinTable(
     *  name="Folders_Calendars",
     *  joinColumns={
     *      @ORM\JoinColumn(name="folder_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="calendar_id", referencedColumnName="id")
     *  }
     * )
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"Folder:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $calendars;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="\App\Entity\Permission",
     *     mappedBy="folderTpl",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     *     )
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->calendars = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Permission $permission
     *
     * @return $this
     */
    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setFolderTpl($this);
        }

        return $this;
    }

    /**
     * @return Calendar[]
     */
    public function getCalendars(): array
    {
        $this->calendars = $this->calendars ?? new ArrayCollection();
        return $this->calendars->getValues();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodEnd(): ?DateTime
    {
        return $this->periodEnd;
    }

    /**
     * @param \DateTime $periodEnd
     */
    public function setPeriodEnd(?DateTime $periodEnd): void
    {
        $this->periodEnd = $periodEnd;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodStart(): ?DateTime
    {
        return $this->periodStart;
    }

    /**
     * @param \DateTime $periodStart
     */
    public function setPeriodStart(?DateTime $periodStart): void
    {
        $this->periodStart = $periodStart;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        $this->permissions = $this->permissions ?? new ArrayCollection();
        return $this->permissions->getValues();
    }

    /**
     * @param \App\Entity\Permission|null $permissions
     *
     * @return $this
     */
    public function setPermissions(?Permission $permissions): self
    {
        $this->permissions = $permissions ?? new ArrayCollection();
        return $this;
    }

    public function removeCalendar(Calendar $calendar): self
    {
        if ($this->calendars->contains($calendar)) {
            $this->calendars->removeElement($calendar);
            $calendar->removeFolder($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Permission $permission
     *
     * @return $this
     */
    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->contains($permission)) {
            $this->permissions->removeElement($permission);
        }

        return $this;
    }

}
