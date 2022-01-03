<?php
/*
 * Core
 * BlockTpl.php
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
use App\Entity\Question\QuestionTpl;
use App\Entity\Questionnaire\QuestionnaireTpl;
use App\Traits\resourceableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Block
 *
 * @ORM\Entity
 *
 * @Gedmo\Loggable
 */
class BlockTpl extends BlockBase
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
     * @Groups({"Superuser:Update"})
     */
    private bool $sample = false;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Question\QuestionTpl", mappedBy="blockTpl", cascade={"persist"},
     *                                                                 fetch="EAGER")
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $questionTpls;


    /**
     * @param \App\Entity\Question\QuestionTpl $questionTpl
     *
     * @return $this
     */
    public function addQuestionTpl(QuestionTpl $questionTpl): self
    {
        if (!$this->questionTpls->contains($questionTpl)) {
            $this->questionTpls->add($questionTpl);
            $questionTpl->setBlockTpl($this);
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
        if (!$this->questionnaireTpls->contains($questionnaireTpl)) {
            $this->questionnaireTpls->add($questionnaireTpl);
            $questionnaireTpl->addBlockTpl($this);
        }

        return $this;
    }

    /**
     * @return QuestionTpl[]
     */
    public function getQuestionTpls(): array
    {
        $this->questionTpls = $this->questionTpls ?? new ArrayCollection();
        // keep only parent questions in list
        $newlist = [];
        foreach ($this->questionTpls as $questionTpl) {
            if (empty($questionTpl->getParent())) {
                $newlist[] = $questionTpl;
            }
        }
        return (new ArrayCollection(array_values($newlist)))->getValues();
    }

    /**
     * @return QuestionnaireTpl[]
     */
    public function getQuestionnaireTpls(): array
    {
        $this->questionnaireTpls = $this->questionnaireTpls ?? new ArrayCollection();
        return $this->questionnaireTpls->getValues();
    }

    /**
     * @return \App\Entity\Block\Block
     */
    public function instantiate(): Block
    {
        $block = new Block();
        $block->setLabel($this->getLabel())
              ->setDescription($this->getDescription())
              ->setBlockTplId($this->getId())
              ->setPosition($this->getPosition());

        foreach ($this->getQuestionTpls() as $questionTpl) {
            $block->addQuestion($questionTpl->instantiate());
        }
        return $block;
    }

    /**
     * @param \App\Entity\Question\QuestionTpl $questionTpl
     *
     * @return $this
     */
    public function removeQuestionTpl(QuestionTpl $questionTpl): self
    {
        if ($this->questionTpls->contains($questionTpl)) {
            $this->questionTpls->removeElement($questionTpl);
            // set the owning side to null (unless already changed)
            if ($questionTpl->getBlockTpl() === $this) {
                $questionTpl->setBlockTpl(null);
            }
        }

        return $this;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTpl $questionnaireTpl
     *
     * @return $this
     * @throws \Exception
     */
    public function removeQuestionnaireTpl(QuestionnaireTpl $questionnaireTpl): self
    {
        if ($this->questionnaireTpls->contains($questionnaireTpl)) {
            $this->questionnaireTpls->removeElement($questionnaireTpl);
            // set the owning side to null (unless already changed)
            if ($questionnaireTpl->getBlockTpls()->contains($this)) {
                $questionnaireTpl->removeBlockTpl($this);
            }
        }

        return $this;
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
}
