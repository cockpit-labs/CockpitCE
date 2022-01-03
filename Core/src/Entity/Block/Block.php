<?php
/*
 * Core
 * Block.php
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

namespace App\Entity\Block;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Question\Question;
use App\Entity\Questionnaire\Questionnaire;
use App\Traits\resourceableEntity;
use App\Traits\scorableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Block
 *
 * @ORM\Entity
 *
 */
class Block extends BlockBase
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
     * @var string
     * @ORM\Column(name="blockTpl_id", type="string", nullable=false)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Score"})
     */
    private $blockTplId = 0;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Questionnaire\Questionnaire", inversedBy="blocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="questionnaire_id", referencedColumnName="id")
     * })
     *
     * @Groups({"Block:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $questionnaire;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Question\Question",
     *     mappedBy="block",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Question\Question $question
     *
     * @return $this
     */
    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setBlock($this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getBlockTplId(): string
    {
        return $this->blockTplId;
    }

    /**
     * @param string $blockTplId
     *
     * @return $this
     */
    public function setBlockTplId(string $blockTplId): self
    {
        $this->blockTplId = $blockTplId;
        return $this;
    }

    /**
     * @return Question[]
     */
    public function getChildEntities(): array
    {
        return $this->questions->getValues();
    }

    /**
     * @return \App\Entity\Questionnaire\Questionnaire|null
     */
    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    /**
     * @param \App\Entity\Questionnaire\Questionnaire|null $questionnaire
     *
     * @return $this
     */
    public function setQuestionnaire(?Questionnaire $questionnaire): self
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        $this->questions = $this->questions ?? new ArrayCollection();
        return $this->questions->getValues();
    }

    /**
     * @param \App\Entity\Question\Question $question
     *
     * @return $this
     */
    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getBlock() === $this) {
                $question->setBlock(null);
            }
        }

        return $this;
    }
}
