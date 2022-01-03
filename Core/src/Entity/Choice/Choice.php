<?php
/*
 * Core
 * Choice.php
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

namespace App\Entity\Choice;

use App\Entity\Question\Question;
use App\Traits\resourceableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Choice
 *
 * @ORM\Entity()
 *
 */
class Choice extends ChoiceBase
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var string
     * @ORM\Column(name="choiceTpl_id", type="string", nullable=false)
     *
     * @Groups({"Question:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     */
    private $choiceTplId;

    /**
     * @var \App\Entity\Question\Question
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\Question", inversedBy="choices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     * })
     *
     */
    private $question;

    /**
     * @return string
     */
    public function getChoiceTplId(): string
    {
        return $this->choiceTplId;
    }

    /**
     * @param string $choiceTplId
     *
     * @return Choice
     */
    public function setChoiceTplId(string $choiceTplId): Choice
    {
        $this->choiceTplId = $choiceTplId;
        return $this;
    }

    /**
     * @return \App\Entity\Question\Question
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * @param \App\Entity\Question\QuestionTpl $question
     *
     * @return ChoiceBase
     */
    public function setQuestion(?Question $question): ChoiceBase
    {
        $this->question = $question;
        return $this;
    }
}
