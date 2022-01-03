<?php
/*
 * Core
 * QuestionnaireTplBlockTpl.php
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

namespace App\Entity\Questionnaire;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Block\BlockTpl;
use App\Traits\resourceableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * QuestionnaireTplBlockTpl
 *
 * @ORM\Table(name="QuestionnaireTpls_BlockTpls")
 * @ORM\Entity
 *
 *
 */
class QuestionnaireTplBlockTpl
{
    /**
     * add a resource (entity name) and iri field automatically filled
     */
    use resourceableEntity;

    /**
     * @var int
     * @ORM\Column(name="position", type="integer", length=255, nullable=false, options={"default" : 1})
     *
     * @Groups({"QuestionnaireTplBlockTpl:Read"})
     * @Groups({"QuestionnaireTplBlockTpl:Update"})
     */
    private $position = 1;

    /**
     * @var QuestionnaireTpl
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Questionnaire\QuestionnaireTpl", inversedBy="questionnaireTplBlockTpls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="questionnaireTpl_id", referencedColumnName="id")
     * })
     *
     * @Groups({"QuestionnaireTplBlockTpl:Read"})
     * @Groups({"QuestionnaireTplBlockTpl:Update"})
     *
         * @ApiProperty(readableLink=false, readable=true)
     */
    private $questionnaireTpl;

    /**
     * @var \App\Entity\Block\BlockTpl
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Block\BlockTpl",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blockTpl_id", referencedColumnName="id")
     * })
     *
     * @Groups({"QuestionnaireTpl:Read"})
     * @Groups({"QuestionnaireTplBlockTpl:Read"})
     * @Groups({"QuestionnaireTplBlockTpl:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $blockTpl;

    /**
     * @return \phpDocumentor\Reflection\Types\String_
     */
    public function __toString(): string
    {
        return $this->getBlockTpl()->getLabel();
    }

    public function getId(): ?string
    {
        return 'questionnaireTpl=' . $this->getQuestionnaireTpl()->getId() . ';' .
            'blockTpl=' . $this->getBlockTpl()->getId();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getBlockTpl(): ?BlockTpl
    {
        return $this->blockTpl;
    }

    public function setBlockTpl(?BlockTpl $blockTpl): self
    {
        $this->blockTpl = $blockTpl;

        return $this;
    }

    public function getQuestionnaireTpl(): ?QuestionnaireTpl
    {
        return $this->questionnaireTpl;
    }

    public function setQuestionnaireTpl(?QuestionnaireTpl $questionnaireTpl): self
    {
        $this->questionnaireTpl = $questionnaireTpl;

        return $this;
    }
}
