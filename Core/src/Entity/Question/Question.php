<?php
/*
 * Core
 * Question.php
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
use App\Entity\Answer\Answer;
use App\Entity\Block\Block;
use App\Entity\Choice\Choice;
use App\Entity\Media\UserMedia;
use App\Traits\resourceableEntity;
use App\Traits\scorableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question
 *
 * @ORM\Entity()
 * ORM\HasLifecycleCallbacks()
 *
 */
class Question extends QuestionBase
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\App\Entity\Choice\Choice", mappedBy="question", cascade={"persist", "remove"})
     * @Assert\NotNull()
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $choices;

    /**
     * One Question has Many Sub Questions.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Question\Question", mappedBy="parent", cascade={"persist"})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $children;

    /**
     * Many Questions have One parent Question.
     *
     * @var Question
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\Question", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", nullable=true, referencedColumnName="id")
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $parent;

    /**
     * @var Block
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Block\Block", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Block_id", referencedColumnName="id")
     * })
     *
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Question:Read"})
     */
    private $block;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Answer\Answer",
     *     mappedBy="question",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Questionnaire:Update"})
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Question:Read"})
     * @Groups({"Question:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $answers;

    /**
     * @var string
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Questionnaire:Update"})
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Question:Read"})
     * @Groups({"Question:Update"})
     *
     */
    private $comment = "";

    /**
     * Many answers can have many photos
     *
     * @var \App\Entity\Media\UserMedia|null
     *
     * @ORM\OneToMany (targetEntity="\App\Entity\Media\UserMedia", mappedBy="question")
     * @ApiProperty(readableLink=false, readable=true)
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Questionnaire:Update"})
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Question:Read"})
     * @Groups({"Question:Update"})
     */
    private $photos;

    /**
     * @var Collection|\App\Entity\Choice\Choice
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Choice\Choice", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="defaultchoice_id", referencedColumnName="id", nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     */
    private $defaultChoice;

    /**
     * @var string
     * @ORM\Column(name="question_tpl_id", type="guid", nullable=true)
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     */
    private $questionTplId;

    public function __construct()
    {
        $this->choices  = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->answers  = new ArrayCollection();

    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Question\Question $child
     *
     * @return $this
     */
    public function addChild(Question $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Choice\Choice $choice
     *
     * @return $this
     */
    public function addChoice(Choice $choice): self
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setQuestion($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Media\UserMedia $photo
     *
     * @return $this
     *
     * @todo control number of photos vs maxphoto in TplQuestion with custom constraint/validator
     */
    public function addPhoto(UserMedia $photo): self
    {
        if (!$this->getPhotos()->contains($photo)) {
            $this->getPhotos()->add($photo);
            $photo->setQuestion($this);
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers(): array
    {
        $this->answers = $this->answers ?? new ArrayCollection();
        return $this->answers->getValues();
    }

    /**
     * @return \App\Entity\Block\Block|null
     */
    public function getBlock(): ?Block
    {
        return $this->block;
    }

    /**
     * @param \App\Entity\Block\Block|null $block
     *
     * @return $this
     */
    public function setBlock(?Block $block): self
    {
        $this->block = $block;
        return $this;
    }

    public function getChildEntities(): array
    {
        return $this->answers->getValues();
    }

    public function getChildren(): array
    {
        $this->children = $this->children ?? new ArrayCollection();
        return $this->children->getValues();
    }

    public function getChoices(): array
    {
        $this->choices = $this->choices ?? new ArrayCollection();
        return $this->choices->getValues();
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return Question
     */
    public function setComment(string $comment): Question
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return \App\Entity\Choice\Choice|null
     */
    public function getDefaultChoice(): ?Choice
    {
        return $this->defaultChoice;
    }

    /**
     * @param \App\Entity\Choice\Choice|null $defaultChoice
     *
     * @return $this
     */
    public function setDefaultChoice(?Choice $defaultChoice): self
    {
        $this->defaultChoice = $defaultChoice;

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
     * @param \App\Entity\Question\Question|null $parent
     *
     * @return $this
     */
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|\App\Entity\Media\UserMedia[]
     */
    public function getPhotos(): Collection
    {
        $this->photos = $this->photos ?? new ArrayCollection();
        return $this->photos;
    }

    /**
     * @return string
     */
    public function getQuestionTplId(): string
    {
        return $this->questionTplId;
    }

    /**
     * @param string $questionTplId
     *
     * @return Question
     */
    public function setQuestionTplId(string $questionTplId): Question
    {
        $this->questionTplId = $questionTplId;
        return $this;
    }

    /**
     * @param \App\Entity\Answer\Answer $Answer
     *
     * @return $this
     */
    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @param \App\Entity\Question\Question $child
     *
     * @return $this
     */
    public function removeChild(Question $child): self
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
     * @param \App\Entity\Choice\Choice $Choice
     *
     * @return $this
     */
    public function removeChoice(Choice $Choice): self
    {
        if ($this->choices->contains($Choice)) {
            $this->choices->removeElement($Choice);
            // set the owning side to null (unless already changed)
            if ($Choice->getQuestion() === $this) {
                $Choice->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @param \App\Entity\Media\UserMedia $photo
     *
     * @return $this
     */
    public function removePhoto(UserMedia $photo): self
    {
        if ($this->getPhotos()->contains($photo)) {
            $this->getPhotos()->removeElement($photo);
            if ($photo->getQuestion() === $this) {
                $photo->setQuestion(null);
            }
        }
        return $this;
    }
//    /**
//     * @ORM\PreUpdate()
//     * @ORM\PrePersist()
//     */
//    public function fixBlock()
//    {
//        if (empty($this->block)) {
//            if (($this->getParent())) {
//                $this->setBlock($this->getParent()->getBlock());
//            }
//        }
//    }
}
