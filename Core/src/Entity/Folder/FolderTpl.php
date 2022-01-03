<?php
/*
 * Core
 * FolderTpl.php
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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Calendar;
use App\Entity\Questionnaire\QuestionnaireTpl;
use App\Traits\resourceableEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Folder
 *
 * @ORM\Entity()
 * @ApiFilter(SearchFilter::class, properties={"permissions.right": "exact", "all": "exact"})
 *
 */
class FolderTpl extends FolderBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var \DateTime
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Folder:Create"})
     */
    public $startDate;

    /**
     * @var \DateTime
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Folder:Create"})
     */
    public $endDate;

    /**
     * @var array<String>
     *
     * @Groups({"FolderTpl:Read"})
     *
     */
    public $targets;

    /**
     * @var array<\App\Entity\Questionnaire\QuestionnaireTpl>
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @ApiProperty(readableLink=true, readable=true)
     */
    public $questionnaireTpls;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Folder",
     *     mappedBy="folderTpl",
     *     fetch="EAGER")
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $folders;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Folder\FolderTplQuestionnaireTpl",
     *     mappedBy="folderTpl",cascade={"persist"},
     *     fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     *
     */
    private $folderTplsQuestionnaireTpls;

    /**
     * @var int
     * @ORM\Column(name="minfolders", type="integer", nullable=true, options={"unsigned"=true})
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"Folder:Create"})
     *
     *         minimum folders count in a period
     *
     */
    private $minFolders = 0;

    /**
     * @var int
     * @ORM\Column(name="maxfolders", type="integer", nullable=true, options={"unsigned"=true})
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"FolderTpl:Periods"})
     * @Groups({"Folder:Create"})
     *
     *         maximum folders count in a period. 0 mean no limit
     */
    private $maxFolders = 0;

    /**
     * @var int
     *
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"FolderTpl:Expectation"})
     * @Groups({"FolderTpl:Periods"})
     * @Groups({"Folder:Create"})
     *
     *         expected folders count in a period. Only calculated
     */
    private $expectedFolders = 0;

    /**
     * @var ArrayCollection
     *
     * @Groups({"FolderTpl:Periods"})
     *
     *         periods. Only calculated
     */
    private $periods = null;

    public function __construct()
    {
        $this->folders                     = new ArrayCollection();
        $this->folderTplsQuestionnaireTpls = new ArrayCollection();
        $this->calendars                   = new ArrayCollection();
        $this->periods                     = new ArrayCollection();
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
            $calendar->addFolderTpl($this);
        }

        return $this;
    }

    /**
     * @param Folder $folder
     *
     * @return $this
     */
    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
        }
        return $this;
    }

    /**
     * @param FolderTplQuestionnaireTpl $folderTplQuestionnaireTpl
     *
     * @return $this
     */
    public function addFolderTplsQuestionnaireTpls(FolderTplQuestionnaireTpl $folderTplQuestionnaireTpl): self
    {
        if (!$this->folderTplsQuestionnaireTpls->contains($folderTplQuestionnaireTpl)) {
            $folderTplQuestionnaireTpl->setFolderTpl($this);
            $this->folderTplsQuestionnaireTpls->add($folderTplQuestionnaireTpl);
        }

        return $this;
    }

    /**
     * @param array $period
     *
     * @return $this
     */
    public function addPeriod(array $period): self
    {
        $this->periods = $this->periods ?? new ArrayCollection();
        if (!$this->periods->contains($period)) {
            $this->periods->add($period);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTpl $questionnaireTpl
     *
     * @return $this
     */
    public function addQuestionnaireTpl(QuestionnaireTpl $questionnaireTpl): self
    {
        $this->questionnaireTpls = $this->questionnaireTpls ?? new ArrayCollection();

        if (!$this->questionnaireTpls->contains($questionnaireTpl)) {
            $this->questionnaireTpls->add($questionnaireTpl);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Target $target
     *
     * @return $this
     */
    public function addTarget(string $target): self
    {
        if (!$this->targets->contains($target)) {
            $this->targets->add($target);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): DateTime
    {
        $this->endDate = $this->endDate ?? new DateTime('1970-01-01');
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int|null
     */
    public function getExpectedFolders(): ?int
    {
        return $this->expectedFolders;
    }

    /**
     * @param int $expectedFolders
     */
    public function setExpectedFolders(int $expectedFolders): void
    {
        $this->expectedFolders = $expectedFolders;
    }

    /**
     * @return FolderTplQuestionnaireTpl[]
     */
    public function getFolderTplsQuestionnaireTpls(): array
    {
        $this->folderTplsQuestionnaireTpls = $this->folderTplsQuestionnaireTpls ?? new ArrayCollection();
        return $this->folderTplsQuestionnaireTpls->getValues();
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
     * @return int|null
     */
    public function getMaxFolders(): ?int
    {
        return $this->maxFolders;
    }

    /**
     * @param int $maxFolders
     *
     * @return $this
     */
    public function setMaxFolders(int $maxFolders): self
    {
        $this->maxFolders = $maxFolders;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinFolders(): ?int
    {
        return $this->minFolders;
    }

    /**
     * @param int $minFolders
     *
     * @return $this
     */
    public function setMinFolders(int $minFolders): self
    {
        $this->minFolders = $minFolders;

        return $this;
    }

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        $this->periods = $this->periods ?? new ArrayCollection();
        return $this->periods->getValues();
    }

    /**
     * @return QuestionnaireTpl[]
     * @throws \Exception
     */
    public function getQuestionnaireTpls(): array
    {
        $this->questionnaireTpls = new ArrayCollection();
        $this->folderTplsQuestionnaireTpls->map(function ($folderTplquestionnaireTpl) {
            $questionnaireTpl = $folderTplquestionnaireTpl->getQuestionnaireTpl();
            $questionnaireTpl->setPosition($folderTplquestionnaireTpl->getPosition());
            $this->addQuestionnaireTpl($questionnaireTpl);
        });

        // sort questionnaireTpls by position
        $iterator = $this->questionnaireTpls->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });
        $this->questionnaireTpls = new ArrayCollection(array_values(iterator_to_array($iterator)));
        return $this->questionnaireTpls->getValues();
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): DateTime
    {
        $this->startDate = $this->startDate ?? new DateTime('1970-01-01');
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return array
     */
    public function getTargets(): array
    {
        $this->targets = $this->targets ?? [];
        return $this->targets;
    }

    /**
     * @param array $targets
     */
    public function setTargets(array $targets): void
    {
        $this->targets = $targets;
    }

    /**
     * @return \App\Entity\Folder\Folder
     * @throws \Exception
     */
    public function instantiate(string $class = Folder::class): Folder
    {
        $folder = new $class();

        $folder->setFolderTpl($this)
               ->setLabel($this->getLabel())
               ->setFolderTplId($this->getId())
               ->setDescription($this->getDescription());
        foreach ($this->calendars as $calendar) {
            $folder->addCalendar($calendar);
        }

        foreach ($this->getQuestionnaireTpls() as $questionnaireTpl) {
            $folder->addQuestionnaire($questionnaireTpl->instantiate());
        }
        return $folder;
    }

    /**
     * @param \App\Entity\Folder\Folder|null $folder
     *
     * @return $this
     */
    public function removeFolder(?Folder $folder = null): self
    {
        if ($folder == null) {
            $this->folders = new ArrayCollection();
        } elseif ($this->folders->contains($folder)) {
            $this->folders->removeElement($folder);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTpl $questionnaireTpl
     *
     * @return $this
     */
    public function removeQuestionnaireTpl(?QuestionnaireTpl $questionnaireTpl = null): self
    {
        $this->questionnaireTpls = $this->questionnaireTpls ?? new ArrayCollection();
        if ($this->questionnaireTpls->contains($questionnaireTpl)) {
            $this->questionnaireTpls->removeElement($questionnaireTpl);
        }

        return $this;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $questionnaireTpls
     *
     * @return FolderTpl
     */
    public function setQuestionnaireTpls(ArrayCollection $questionnaireTpls): FolderTpl
    {
        $this->questionnaireTpls = $questionnaireTpls;
        return $this;
    }

    /**
     * @return $this
     */
    public function sortQuestionnnaireTpls(): self
    {
        $position = 0;
        foreach ($this->getFolderTplsQuestionnaireTpls() as &$folderTplsQuestionnaireTpl) {
            $folderTplsQuestionnaireTpl->setPosition($position++);
        }
        return $this;
    }
}
