<?php
/*
 * Core
 * QuestionnaireTpl.php
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

namespace App\Entity\Questionnaire;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Block\BlockTpl;
use App\Entity\Folder\FolderTpl;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * QuestionnaireTpl
 *
 * @ORM\Entity
 *
 */
class QuestionnaireTpl extends QuestionnaireBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var array<\App\Entity\Block\BlockTpl>
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     */
    public $blockTpls;

    /**
     * @var ArrayCollection
     *
     */
    private $folderTpls;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Questionnaire\QuestionnaireTplBlockTpl",
     *     mappedBy="questionnaireTpl",
     *     cascade={"persist"},
     *     fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @Groups({"QuestionnaireTpl:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $questionnaireTplBlockTpls;

    /**
     * @var bool
     * @ORM\Column(name="sample", type="boolean", options={"default":false})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"Superuser:Update"})
     */
    private bool $sample = false;

    public function __construct()
    {
        $this->blockTpls                 = new ArrayCollection();
        $this->folderTpls                = new ArrayCollection();
        $this->questionnaireTplBlockTpls = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Block\BlockTpl $blockTpl
     *
     * @return $this
     */
    public function addBlockTpl(BlockTpl $blockTpl): self
    {
        $this->blockTpls = $this->blockTpls ?? new ArrayCollection();
        if (!$this->blockTpls->contains($blockTpl)) {
            $this->blockTpls->add($blockTpl);
        }
        return $this;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl $folderTpl
     *
     * @return $this
     */
    public function addFolderTpl(FolderTpl $folderTpl): self
    {
        if (!$this->folderTpls->contains($folderTpl)) {
            $this->folderTpls->add($folderTpl);
            $folderTpl->addQuestionnaireTpl($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTplBlockTpl $questionnaireTplBlockTpl
     *
     * @return $this
     */
    public function addQuestionnaireTplBlockTpls(QuestionnaireTplBlockTpl $questionnaireTplBlockTpl): self
    {
        if (!$this->questionnaireTplBlockTpls->contains($questionnaireTplBlockTpl)) {
            $questionnaireTplBlockTpl->setQuestionnaireTpl($this);
            $this->questionnaireTplBlockTpls->add($questionnaireTplBlockTpl);
        }

        return $this;
    }

    /**
     * @return BlockTpl[]
     * @throws \Exception
     */
    public function getBlockTpls(): array
    {
        // (re)construct blockTpls from QuestionnaireTplBlockTpls
        $this->blockTpls = new ArrayCollection();
        $this->questionnaireTplBlockTpls->map(function ($questionnaireTplBlockTpl) {
            $blockTpl = $questionnaireTplBlockTpl->getBlockTpl();
            $blockTpl->setPosition($questionnaireTplBlockTpl->getPosition());
            $this->addBlockTpl($blockTpl);
        });

        // sort blockTpls by position
        $iterator = $this->blockTpls->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });
        $this->blockTpls = new ArrayCollection(array_values(iterator_to_array($iterator)));
        return $this->blockTpls->getValues();
    }

    /**
     * @return FolderTpl[]
     */
    public function getFolderTpls(): array
    {
        $this->folderTpls = $this->folderTpls ?? new ArrayCollection;
        return $this->folderTpls->getValues();
    }

    /**
     * @return QuestionnaireTplBlockTpl[]
     */
    public function getQuestionnaireTplBlockTpls(): array
    {
        $this->questionnaireTplBlockTpls = $this->questionnaireTplBlockTpls ?? new ArrayCollection;
        return $this->questionnaireTplBlockTpls->getValues();
    }

    /**
     * @return \App\Entity\Questionnaire\Questionnaire
     * @throws \Exception
     */
    public function instantiate(): Questionnaire
    {
        $questionnaire = new Questionnaire();

        $questionnaire->setLabel($this->getLabel())
                      ->setDescription($this->getDescription())
                      ->setQuestionnaireTplId($this->getId())
                      ->setPosition($this->getPosition());
        foreach ($this->getBlockTpls() as $blockTpl) {
            $questionnaire->addBlock($blockTpl->instantiate());
        }

        return $questionnaire;
    }

    /**
     * @return bool
     */
    public function isSample(): bool
    {
        return $this->sample;
    }

    /**
     * @param bool $sample
     *
     * @return self
     */
    public function setSample(bool $sample): self
    {
        $this->sample = $sample;
        return $this;
    }

    /**
     * @param \App\Entity\Block\BlockTpl|null $blockTpl
     *
     * @return $this
     */
    public function removeBlockTpl(BlockTpl $blockTpl = null): self
    {
        $this->blockTpls = $this->blockTpls ?? new ArrayCollection();
        if ($this->blockTpls->contains($blockTpl)) {
            $this->blockTpls->removeElement($blockTpl);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl|null $folderTpl
     *
     * @return $this
     */
    public function removeFolderTpl(?FolderTpl $folderTpl): self
    {
        if ($this->folderTpls->contains($folderTpl)) {
            $this->folderTpls->removeElement($folderTpl);
            $folderTpl->removeQuestionnaireTpl($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTplBlockTpl $questionnaireTplBlockTpl
     *
     * @return $this
     */
    public function removeQuestionnaireTplBlockTpls(QuestionnaireTplBlockTpl $questionnaireTplBlockTpl): self
    {
        if ($this->questionnaireTplBlockTpls->contains($questionnaireTplBlockTpl)) {
            $this->questionnaireTplBlockTpls->removeElement($questionnaireTplBlockTpl);
            // set the owning side to null (unless already changed)
            if ($questionnaireTplBlockTpl->getQuestionnaireTpl() === $this) {
                $questionnaireTplBlockTpl->setQuestionnaireTpl(null);
            }
        }

        return $this;
    }
}
