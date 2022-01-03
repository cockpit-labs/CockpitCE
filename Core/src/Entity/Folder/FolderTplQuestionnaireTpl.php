<?php
/*
 * Core
 * FolderTplQuestionnaireTpl.php
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

namespace App\Entity\Folder;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Questionnaire\QuestionnaireTpl;
use App\Traits\resourceableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * FolderTplQuestionnaireTpl
 *
 * @ORM\Table(name="FolderTpls_QuestionnaireTpls")
 * @ORM\Entity
 *
 */
class FolderTplQuestionnaireTpl
{
    /**
     * add a resource field filled with entity name
     */
    use resourceableEntity;

    /**
     * @var \App\Entity\Folder\FolderTpl
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Folder\FolderTpl",
     *     inversedBy="folderTplsQuestionnaireTpls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="folderTpl_id", referencedColumnName="id")
     * })
     *
     * @Groups({"FolderTplQuestionnaireTpl:Read"})
     * @Groups({"FolderTplQuestionnaireTpl:Update"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    public $folderTpl;

    /**
     * @var int
     * @ORM\Column(name="position", type="integer", length=255, nullable=false, options={"default" : 1})
     *
     * @Groups({"FolderTplQuestionnaireTpl:Read"})
     * @Groups({"FolderTplQuestionnaireTpl:Update"})
     */
    private $position = 1;

    /**
     * @var QuestionnaireTpl
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\Questionnaire\QuestionnaireTpl")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="questionnaireTpl_id", referencedColumnName="id")
     * })
     *
     * @Groups({"FolderTplQuestionnaireTpl:Read"})
     * @Groups({"FolderTplQuestionnaireTpl:Update"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $questionnaireTpl;

    /**
     * @return \App\Entity\Folder\FolderTpl|null
     */
    public function getFolderTpl(): ?FolderTpl
    {
        return $this->folderTpl;
    }

    /**
     * @param \App\Entity\Folder\FolderTpl|null $folderTpl
     *
     * @return $this
     */
    public function setFolderTpl(?FolderTpl $folderTpl): self
    {
        $this->folderTpl = $folderTpl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return 'questionnaireTpl=' . $this->getQuestionnaireTpl()->getId() . ';' .
            'folderTpl=' . $this->getFolderTpl()->getId();
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
     *
     * @return FolderTplQuestionnaireTpl
     */
    public function setPosition(int $position): ?FolderTplQuestionnaireTpl
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return \App\Entity\Questionnaire\QuestionnaireTpl|null
     */
    public function getQuestionnaireTpl(): ?QuestionnaireTpl
    {
        return $this->questionnaireTpl;
    }

    /**
     * @param \App\Entity\Questionnaire\QuestionnaireTpl|null $questionnaireTpl
     *
     * @return $this
     */
    public function setQuestionnaireTpl(?QuestionnaireTpl $questionnaireTpl): self
    {
        $this->questionnaireTpl = $questionnaireTpl;

        return $this;
    }
}
