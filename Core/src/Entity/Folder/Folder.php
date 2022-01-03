<?php
/*
 * Core
 * Folder.php
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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Calendar;
use App\Entity\Questionnaire\Questionnaire;
use App\Entity\Right;
use App\Traits\resourceableEntity;
use App\Traits\scorableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Folder
 *
 * @ORM\Entity(repositoryClass="App\Repository\FolderRepository")
 *
 * @ApiFilter(DateFilter::class,
 *                  properties={
 *                      "createdAt",
 *                      "updatedAt"
 *                  }
 *     )
 *
 * @ApiFilter(SearchFilter::class,
 *                  properties={
 *                      "folderTpl.id": "exact",
 *                      "state": "exact",
 *                      "appliedTo": "exact",
 *                      "parentGroups": "partial"
 *                  }
 *     )
 * @ApiFilter(OrderFilter::class, properties={"createdAt", "updatedAt"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 *
 */
class Folder extends FolderBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * Add scoring field
     * a method named 'getChildEntities' must exists to process score on child Entities if there is child entities
     * a method named 'processScore' must exists to process score on current entity, if there is no child entities (end
     * tree entity)
     */
    use scorableEntity;

    /**
     * Hook SoftDeleteable behavior
     * updates deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="applied_on", type="string", length=36,
     *   nullable=false, options={"comment"="user or group id"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Score"})
     */
    private $appliedTo;


    /**
     * @var string
     *
     * @ORM\Column(name="parent_groups", type="string", length=1000, nullable=false)
     * @Groups({"Folder:Read"})
     * @Groups({"Score"})
     */
    private $parentGroups;

    /**
     * @var \App\Entity\Folder\FolderTpl
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Folder\FolderTpl", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderTpl_id", referencedColumnName="id")
     * })
     * @ApiProperty(readableLink=false, readable=true)
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     */
    private $folderTpl;

    /**
     * @var string
     * @Groups({"Folder:Read"})
     */
    private $folderTplId;

    /**
     * @var bool
     * @Groups({"Folder:Read"})
     */
    private $updatable = false;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Questionnaire\Questionnaire",
     *     mappedBy="folder",
     *     cascade={"persist", "remove"})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Score"})
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $questionnaires;

    public function __construct()
    {
        $this->calendars      = new ArrayCollection();
        $this->questionnaires = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Calendar $calendar
     *
     * @return $this
     */
    public function addCalendar(Calendar $calendar): self
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars->add($calendar);

            $calendar->addFolder($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\Questionnaire $questionnaire
     *
     * @return $this
     */
    public function addQuestionnaire(Questionnaire $questionnaire): self
    {
        if (!$this->questionnaires->contains($questionnaire)) {
            $this->questionnaires->add($questionnaire);
            $questionnaire->setFolder($this);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getChildEntities(): array
    {
        return $this->questionnaires->getValues();
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
     * @return string
     */
    public function getFolderTplId(): string
    {
        if (empty($this->folderTplId)) {
            $this->folderTplId = $this->getFolderTpl()->getId();
        }
        return $this->folderTplId;
    }

    /**
     * @param string $folderTplId
     *
     * @return $this
     */
    public function setFolderTplId(?string $folderTplId): self
    {
        $this->folderTplId = $folderTplId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentGroups(): ?string
    {
        return $this->parentGroups;
    }

    /**
     * @param string $parentGroups
     *
     * @return $this
     */
    public function setParentGroups(string $parentGroups): self
    {
        $this->parentGroups = $parentGroups;

        return $this;
    }

    /**
     * @return Questionnaire[]
     */
    public function getQuestionnaires(): array
    {
        $this->questionnaires = $this->questionnaires ?? new ArrayCollection();
        return $this->questionnaires->getValues();
    }

    /**
     * @return bool
     */
    public function getUpdatable(): bool
    {
        return $this->updatable;
    }

    /**
     * @param string $updatable
     *
     * @return Folder
     */
    public function setUpdatable($updatable): self
    {
        $this->updatable = $updatable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getappliedTo(): ?string
    {
        return $this->appliedTo;
    }

    /**
     * @param string $appliedTo
     *
     * @return $this
     */
    public function setappliedTo(string $appliedTo): self
    {
        $this->appliedTo = $appliedTo;

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\Questionnaire $questionnaire
     *
     * @return $this
     */
    public function removeQuestionnaire(Questionnaire $questionnaire): self
    {
        if ($this->questionnaires->contains($questionnaire)) {
            $this->questionnaires->removeElement($questionnaire);
            // set the owning side to null (unless already changed)
            if ($questionnaire->getFolder() === $this) {
                $questionnaire->setFolder(null);
            }
        }

        return $this;
    }

}
