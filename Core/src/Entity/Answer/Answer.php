<?php
/*
 * Core
 * Answer.php
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

namespace App\Entity\Answer;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Choice\Choice;
use App\Entity\Media\UserMedia;
use App\Entity\Question\Question;
use App\Traits\resourceableEntity;
use App\Traits\scorableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Answer
 *
 * @ORM\Table(name="Answer")
 * @ORM\Entity
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 *
 */
class Answer
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

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
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @Groups({"Answer:Read"})
     * @Groups({"Answer:Update"})
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Update"})
     */
    public $id;

    /**
     * @var \App\Entity\Question\Question
     *
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\Question", inversedBy="answers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     * })
     *
     * @Groups({"Answer:Read"})
     */
    private $question;

    /**
     * @var \App\Entity\Choice\Choice
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Choice\Choice")
     * @ORM\JoinColumn(name="choice_id", referencedColumnName="id")
     *
     * @Groups({"Answer:Read"})
     * @Groups({"Answer:Update"})
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
     * @Gedmo\Versioned
     */
    private $choice;

    /**
     * Many answers can have many photos
     *
     * @var \App\Entity\Media\UserMedia|null
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Media\UserMedia", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     *
     * @Groups({"Answer:Read"})
     * @Groups({"Answer:Update"})
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
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $media;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", nullable=true)
     *
     * @Groups({"Answer:Read"})
     * @Groups({"Answer:Update"})
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
    private $value;

    /**
     * @var string
     * @ORM\Column(name="rawValue", type="string", nullable=true)
     *
     * @Groups({"Answer:Read"})
     * @Groups({"Answer:Update"})
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
    private $rawValue;

    /**
     * @return \App\Entity\Choice\Choice
     */
    public function getChoice(): ?Choice
    {
        return $this->choice;
    }

    /**
     * @param \App\Entity\Choice\Choice|null $choice
     *
     * @return $this
     */
    public function setChoice(?Choice $choice): self
    {
        $this->choice = $choice;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Answer
     */
    public function setId(string $id): Answer
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \App\Entity\Media\UserMedia|null
     */
    public function getMedia(): ?UserMedia
    {
        return $this->media;
    }

    /**
     * @param \App\Entity\Media\UserMedia|null $media
     *
     * @return $this
     */
    public function setMedia(?UserMedia $media): self
    {
        $this->media = $media;
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
     * @param \App\Entity\Question\Question|null $question
     *
     * @return $this
     */
    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawValue(): ?string
    {
        return $this->rawValue;
    }

    /**
     * @param string|null $rawValue
     *
     * @return $this
     */
    public function setRawValue(?string $rawValue): self
    {
        $this->rawValue = $rawValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return $this|null
     */
    public function processScore(): ?self
    {
        if (empty($this->getChoice())) {
            return $this;
        }

        $expressionEngine = new ExpressionLanguage();
        $raw              = $this->getRawValue();
        if (is_numeric($raw)) {
            $raw = floatval($raw);
        } else {
            $raw = '"' . $raw . '"';
        }

        $valueFormula    = $this->getChoice()->getValueFormula();
        $valueCalculated = 0;
        if (empty($valueFormula['expression'])) {
            $valueCalculated = is_numeric($raw) ? $raw : 0;
        } else {
            $valueCalculated = floatval($expressionEngine->evaluate($valueFormula['expression'], ['value' => $raw]));
        }
        $this->setValue($valueCalculated);
        $this->setScore($valueCalculated * $this->getQuestion()->getWeight());
        return $this;
    }
}
