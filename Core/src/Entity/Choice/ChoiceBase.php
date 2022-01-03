<?php
/*
 * Core
 * ChoiceBase.php
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

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Media\MediaTpl;
use App\Traits\labelableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Choice
 *
 * @ORM\Table(name="Choices")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *     "Template"="ChoiceTpl",
 *     "Instance"="Choice"
 * })
 * @ORM\HasLifecycleCallbacks()
 *
 */
abstract class ChoiceBase implements Translatable
{
    /**
     * add a label field
     */
    use labelableEntity;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"ChoiceTpl:Read"})
     * @Groups({"ChoiceTpl:Update"})
     *
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Answer:Read"})
     * @Groups({"Folder:Read"})
     * @Groups({"Question:Read"})
     * @Groups({"Questionnaire:Read"})
     */
    public $id;
    /**
     * @var MediaTpl|null
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Media\MediaTpl", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true)
     * @ApiProperty(iri="http://schema.org/image",readableLink=false, readable=true)
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"ChoiceTpl:Read"})
     * @Groups({"ChoiceTpl:Update"})
     *
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Folder:Read"})
     * @Groups({"Question:Read"})
     * @Groups({"Questionnaire:Read"})
     */
    public $media;
    /**
     * @var int
     * @ORM\Column(name="position", type="integer", nullable=false, options={"default" : 1})
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"ChoiceTpl:Read"})
     * @Groups({"ChoiceTpl:Update"})
     *
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Folder:Read"})
     * @Groups({"Question:Read"})
     * @Groups({"Questionnaire:Read"})
     *
     */
    private $position = 1;
    /**
     * this field is filled with a formula to calculate the value of the choice, ie
     *  =10
     *
     * @ORM\Column(name="value_formula", type="json", length=255, nullable=false)
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTpl:Update"})
     * @Groups({"BlockTpl:Read"})
     * @Groups({"BlockTpl:Update"})
     * @Groups({"QuestionTpl:Read"})
     * @Groups({"QuestionTpl:Update"})
     * @Groups({"ChoiceTpl:Read"})
     * @Groups({"ChoiceTpl:Update"})
     *
     * @Groups({"Block:Read"})
     * @Groups({"Block:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Folder:Instantiate"})
     * @Groups({"Folder:Read"})
     * @Groups({"Question:Read"})
     * @Groups({"Questionnaire:Read"})
     *
     */
    // ToDo: validate valueFormula syntax
    private $valueFormula='{"expression": "value"}';

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMedia(): ?MediaTpl
    {
        return $this->media;
    }

    public function setMedia(?MediaTpl $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getValueFormula(): ?array
    {
        return $this->valueFormula;
    }

    public function setValueFormula(array $valueFormula): self
    {
        $this->valueFormula = $valueFormula;

        return $this;
    }
    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function fixValueFormula()
    {
        if(empty($this->valueFormula)){
            $this->setValueFormula(['expression'=>$this->getPosition()]);
        }
    }
}
