<?php
/*
 * Core
 * QuestionTpl.php
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

namespace App\Entity\Question;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Block\BlockTpl;
use App\Entity\Choice\ChoiceTpl;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * QuestionTpl
 *
 * @ORM\Entity()
 *
 */
class QuestionTpl extends QuestionBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var bool
     * @ORM\Column(name="sample", type="boolean", options={"default":false})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"Superuser:Update"})
     */
    private bool $sample = false;

    /**
     * @var ChoiceTpl
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Choice\ChoiceTpl", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="defaultchoicetpl_id", referencedColumnName="id", nullable=true)
     *
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     */
    public $defaultChoiceTpl;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Choice\ChoiceTpl", mappedBy="questionTpl", cascade={"persist"})
     * @Assert\NotNull()
     *
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     *
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    // ToDo: check if each questionTpl has a choiceTpl
    private $choiceTpls;

    /**
     * @var ArrayCollection
     * One QuestionTpl has Many Sub Questions.
     * @ORM\OneToMany(targetEntity="\App\Entity\Question\QuestionTpl", mappedBy="parent", cascade={"persist"})
     *
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     */
    private $children;

    /**
     * Many Questions have One parent QuestionTpl.
     *
     * @var QuestionTpl
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\QuestionTpl", inversedBy="children")
     * @ORM\JoinColumn(name="tplparent_id", nullable=true, referencedColumnName="id")
     *
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $parent;

    /**
     * @var \App\Entity\Block\BlockTpl
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Block\BlockTpl", inversedBy="questionTpls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blockTpl_id", referencedColumnName="id")
     * })
     *
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     */
    private $blockTpl;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->choiceTpls = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Question\QuestionTpl $child
     *
     * @return $this
     */
    public function addChild(QuestionTpl $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Choice\ChoiceTpl $choiceTpl
     *
     * @return $this
     */
    public function addChoiceTpl(ChoiceTpl $choiceTpl): self
    {
        if (!$this->choiceTpls->contains($choiceTpl)) {
            $this->choiceTpls->add($choiceTpl);
            $choiceTpl->setQuestionTpl($this);
        }

        return $this;
    }

    /**
     * @return \App\Entity\Block\BlockTpl|null
     */
    public function getBlockTpl(): ?BlockTpl
    {
        return $this->blockTpl;
    }

    /**
     * @param \App\Entity\Block\BlockTpl|null $blockTpl
     *
     * @return $this
     */
    public function setBlockTpl(?BlockTpl $blockTpl): self
    {
        $this->blockTpl = $blockTpl;

        return $this;
    }

    /**
     * @return QuestionTpl[]
     */
    public function getChildren(): array
    {
        $this->children = $this->children ?? new ArrayCollection();
        return $this->children->getValues();
    }

    /**
     * @return ChoiceTpl[]
     */
    public function getChoiceTpls(): array
    {
        $this->choiceTpls = $this->choiceTpls ?? new ArrayCollection();
        return $this->choiceTpls->getValues();
    }

    /**
     * @return \App\Entity\Choice\ChoiceTpl|null
     */
    public function getDefaultChoiceTpl(): ?ChoiceTpl
    {
        return $this->defaultChoiceTpl;
    }

    public function setDefaultChoiceTpl(?ChoiceTpl $defaultChoiceTpl): self
    {
        $this->defaultChoiceTpl = $defaultChoiceTpl;

        return $this;
    }

    /**
     * @return $this|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param \App\Entity\Question\QuestionTpl|null $parent
     *
     * @return $this
     */
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     *
     * @return \App\Entity\Question\Question
     */
    public function instantiate(): Question
    {
        $question = new Question();
        $question->setLabel($this->getLabel())
                 ->setDescription($this->getDescription())
                 ->setQuestionTplId($this->getId())
                 ->setCategory($this->getCategory())
                 ->setAlias($this->getAlias())
                 ->setPosition($this->getPosition())
                 ->setMandatory($this->getMandatory())
                 ->setMaxChoices($this->getMaxChoices())
                 ->setMaxPhotos($this->getMaxPhotos())
                 ->setMinChoices($this->getMinChoices())
                 ->setExternalUrl($this->getExternalUrl())
                 ->setHasComment($this->getHasComment())
                 ->setHiddenLabel($this->getHiddenLabel())
                 ->setReadRenderer($this->getReadRenderer())
                 ->setWeight($this->getWeight())
                 ->setWriteRenderer($this->getWriteRenderer())
                 ->setTrigger($this->getTrigger())
                 ->setValidator($this->getValidator());

        foreach ($this->choiceTpls as $choiceTpl) {
            $choice = $choiceTpl->instantiate();
            $question->addChoice($choice);
            if ($this->getDefaultChoiceTpl() === $choiceTpl) {
                $question->setDefaultChoice($choice);
            }
        }

        foreach ($this->children as $childTpl) {
            $child = $childTpl->instantiate();
            $child->setParent($question);
            $question->addChild($child);
        }
        return $question;
    }

    public function removeChild(QuestionTpl $child): self
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

    public function removeChoiceTpl(ChoiceTpl $choiceTpl): self
    {
        if ($this->choiceTpls->contains($choiceTpl)) {
            $this->choiceTpls->removeElement($choiceTpl);
            // set the owning side to null (unless already changed)
            if ($choiceTpl->getQuestionTpl() === $this) {
                $choiceTpl->setQuestionTpl(null);
            }
        }
        return $this;
    }

//    /**
//     * @ORM\PreUpdate()
//     * @ORM\PrePersist()
//     */
//    public function fixBlockTpl()
//    {
//        if (empty($this->blockTpl)) {
//            if (($this->getParent())) {
//                $this->setBlockTpl($this->getParent()->getBlockTpl());
//            }
//        }
//    }
//
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
}
