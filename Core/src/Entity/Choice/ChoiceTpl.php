<?php
/*
 * Core
 * ChoiceTpl.php
 *
 * Copyright (c) 2020 Sentinelo
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

namespace App\Entity\Choice;

use App\Entity\Question\QuestionTpl;
use App\Traits\resourceableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Question
 *
 * @ORM\Entity()
 *
 */
class ChoiceTpl extends ChoiceBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var \App\Entity\Question\QuestionTpl
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\QuestionTpl", inversedBy="choiceTpls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="questionTpl_id", referencedColumnName="id")
     * })
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"ChoiceTpl:Read"})
     * @Groups({"ChoiceTpl:Update"})
     */
    private $questionTpl;


    public function getQuestionTpl(): ?QuestionTpl
    {
        return $this->questionTpl;
    }

    public function setQuestionTpl(?QuestionTpl $questionTpl): self
    {
        $this->questionTpl = $questionTpl;

        return $this;
    }


    public function instantiate(): Choice
    {

        $choice = new Choice();
        $choice->setMedia($this->getMedia())
               ->setValueFormula($this->getValueFormula())
               ->setLabel($this->getLabel())
               ->setChoiceTplId($this->getId())
               ->setPosition($this->getPosition());

        return $choice;
    }
}
