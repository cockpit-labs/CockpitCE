<?php
/*
 * Core
 * Questionnaire.php
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

namespace App\Entity\Questionnaire;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Block\Block;
use App\Entity\Folder\Folder;
use App\Entity\Media\QuestionnairePDFMedia;
use App\Traits\resourceableEntity;
use App\Traits\scorableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Questionnaire
 *
 * @ORM\Entity
 * ORM\HasLifecycleCallbacks()
 */
class Questionnaire extends QuestionnaireBase
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
     * @var \App\Entity\Media\QuestionnairePDFMedia|null
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Media\QuestionnairePDFMedia", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pdf_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Questionnaire:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $pdf;
    /**
     * @var \App\Entity\Folder\Folder
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Folder\Folder", inversedBy="questionnaires")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folder_id", referencedColumnName="id")
     * })
     * @Groups({"Questionnaire:Read"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $folder;

    /**
     * @var string
     * @ORM\Column(name="questionnaireTpl_id", type="guid", nullable=true)
     * @Groups({"Folder:Read"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Score"})
     */
    private $questionnaireTplId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Block\Block", mappedBy="questionnaire", cascade={"persist", "remove"})
     *
     * @Groups({"Folder:Read"})
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Score"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $blocks;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    /**
     * @param \App\Entity\Block\Block $block
     *
     * @return $this
     */
    public function addBlock(Block $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->setQuestionnaire($this);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        $this->blocks = $this->blocks ?? new ArrayCollection;
        return $this->blocks->getValues();
    }

    /**
     * @return array
     */
    public function getChildEntities(): array
    {
        return $this->blocks->getValues();
    }

    /**
     * @return \App\Entity\Folder\Folder|null
     */
    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    /**
     * @param \App\Entity\Folder\Folder|null $folder
     *
     * @return $this
     */
    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * @return \App\Entity\Media\QuestionnairePDFMedia|null
     */
    public function getPdf(): ?QuestionnairePDFMedia
    {
        return $this->pdf;
    }

    /**
     * @param \App\Entity\Media\QuestionnairePDFMedia|null $pdf
     *
     * @return $this
     */
    public function setPdf(?QuestionnairePDFMedia $pdf): self
    {
        $this->pdf = $pdf;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuestionnaireTplId(): string
    {
        return $this->questionnaireTplId;
    }

    /**
     * @param string $questionnaireTplId
     *
     * @return $this
     */
    public function setQuestionnaireTplId(string $questionnaireTplId): self
    {
        $this->questionnaireTplId = $questionnaireTplId;
        return $this;
    }

    /**
     * @param \App\Entity\Block\Block $block
     *
     * @return $this
     */
    public function removeBlock(Block $block): self
    {
        if ($this->blocks->contains($block)) {
            $this->blocks->removeElement($block);
            // set the owning side to null (unless already changed)
            if ($block->getQuestionnaire() === $this) {
                $block->setQuestionnaire(null);
            }
        }

        return $this;
    }

}
