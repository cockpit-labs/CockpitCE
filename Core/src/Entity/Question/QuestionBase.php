<?php
/*
 * Core
 * QuestionBase.php
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

use App\Entity\Category;
use App\Traits\descriptionableEntity;
use App\Traits\labelableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseQuestion
 *
 * @ORM\Table(
 *     name="Questions",
 *     indexes={
 *          @ORM\Index(name="BaseQuestiondeleted_idx", columns={"deleted_at"}),
 *     }
 * )
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *     "Template"="QuestionTpl",
 *     "Instance"="Question"
 * })
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
abstract class QuestionBase
{
    /**
     * Hook blameable behavior
     * updates createdBy, updatedBy fields
     */
    use BlameableEntity;

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Hook SoftDeleteable behavior
     * updates deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * add a label field
     */
    use labelableEntity;

    /**
     * add a description field
     */
    use descriptionableEntity;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Questionnaire:Update"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    public $id;
    /**
     * @var int|null
     * @Assert\Range(min = 0, max = 100)
     *
     * @ORM\Column(name="weight", type="integer", nullable=true, options={"unsigned"=true}, options={"default" : 0})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    public $weight = 0;
    /**
     * @var int
     * @Assert\PositiveOrZero
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    public $position = 0;
    /**
     * @var int
     *
     * @ORM\Column(name="max_choices", type="integer", nullable=false, options={"default" : 0})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    public $maxChoices = 0;
    /**
     * @var \App\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    public $category;
    /**
     * @ORM\Column(name="external_url", type="json", length=255, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     *
     */
    private $externalUrl;
    /**
     * @var string|null
     * @ORM\Column(name="alias", type="string", length=255, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     *
     */
    private $alias;
    /**
     * @ORM\Column(name="read_renderer", type="json", length=500, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $readRenderer;
    /**
     * @ORM\Column(name="write_renderer", type="json", length=500, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $writeRenderer;
    /**
     * @ORM\Column(name="validator", type="json", length=255, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $validator;
    /**
     * @ORM\Column(name="open_trigger", type="json", length=500, nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     *
     */
    // ToDo: validate trigger syntax
    private $trigger;
    /**
     * @var bool|null
     * @Assert\Type("bool")
     *
     * @ORM\Column(name="hiddenlabel", type="boolean", nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $hiddenLabel = false;
    /**
     * @var bool|null
     * @Assert\Type("bool")
     *
     * @ORM\Column(name="mandatory", type="boolean", nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $mandatory = false;
    /**
     * @var bool|null
     * @Assert\Type("bool")
     *
     * @ORM\Column(name="has_comment", type="boolean", nullable=true)
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $hasComment = false;
    /**
     * @var int
     * @Assert\Range(min = 0, max = 10)
     *
     * @ORM\Column(name="max_photos", type="integer", nullable=false, options={"default" : 0})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $maxPhotos = 0;
    /**
     * @var int
     *
     * @ORM\Column(name="min_choices", type="integer", nullable=false, options={"default" : 0})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Block:Read"})
     * @Groups({"Question:Read"})
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     */
    private $minChoices = 0;

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     *
     * @return $this
     */
    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return \App\Entity\Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param \App\Entity\Category|null $category
     *
     * @return $this
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getExternalUrl(): ?array
    {
        return $this->externalUrl;
    }

    /**
     * @param string|null $externalUrl
     *
     * @return QuestionBase
     */
    public function setExternalUrl(?array $externalUrl): self
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getHasComment(): ?bool
    {
        return $this->hasComment;
    }

    /**
     * @param bool|null $hasComment
     *
     * @return $this
     */
    public function setHasComment(?bool $hasComment): self
    {
        $this->hasComment = $hasComment;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getHiddenLabel(): ?bool
    {
        return $this->hiddenLabel;
    }

    /**
     * @param bool|null $hiddenLabel
     *
     * @return $this
     */
    public function setHiddenLabel(?bool $hiddenLabel): self
    {
        $this->hiddenLabel = $hiddenLabel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return bool|null
     */
    public function getMandatory(): ?bool
    {
        return $this->mandatory;
    }

    /**
     * @param bool|null $mandatory
     *
     * @return $this
     */
    public function setMandatory(?bool $mandatory): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxChoices(): int
    {
        return $this->maxChoices;
    }

    /**
     * @param int $maxChoices
     *
     * @return $this
     */
    public function setMaxChoices(int $maxChoices): self
    {
        $this->maxChoices = $maxChoices;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxPhotos(): ?int
    {
        return $this->maxPhotos;
    }

    /**
     * @param int $maxPhotos
     *
     * @return $this
     */
    public function setMaxPhotos(int $maxPhotos): self
    {
        $this->maxPhotos = $maxPhotos;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinChoices(): int
    {
        return $this->minChoices;
    }

    /**
     * @param int $minChoices
     *
     * @return $this
     */
    public function setMinChoices(int $minChoices): self
    {
        $this->minChoices = $minChoices;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getReadRenderer(): ?array
    {
        return $this->readRenderer;
    }

    /**
     * @param array|null $readRenderer
     *
     * @return $this
     */
    public function setReadRenderer(?array $readRenderer): self
    {
        $this->readRenderer = $readRenderer;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTrigger(): ?array
    {
        return $this->trigger;
    }

    /**
     * @param array|null $trigger
     *
     * @return $this
     */
    public function setTrigger(?array $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getValidator(): ?array
    {
        return $this->validator;
    }

    /**
     * @param array|null $validator
     *
     * @return $this
     */
    public function setValidator(?array $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @param int|null $weight
     *
     * @return $this
     */
    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getWriteRenderer(): ?array
    {
        return $this->writeRenderer;
    }

    /**
     * @param array|null $writeRenderer
     *
     * @return $this
     */
    public function setWriteRenderer(?array $writeRenderer): self
    {
        $this->writeRenderer = $writeRenderer;

        return $this;
    }
}
