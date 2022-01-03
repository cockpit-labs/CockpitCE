<?php
/*
 * Core
 * UserMedia.php
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

namespace App\Entity\Media;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Entity\Folder\Folder;
use App\Entity\Question\Question;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ApiFilter(OrderFilter::class, properties={"createdAt", "updatedAt"})
 */
class UserMedia extends Media
{
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Media\MediaOwner",
     *     mappedBy="media",
     *     cascade={"persist", "remove"})
     *
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private $owners;

    /**
     * @var \App\Entity\Question\Question|null
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Question\Question",
     *     inversedBy="photos")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     */
    private ?Question $question;

    /**
     * @var string
     * @ORM\Column(name="target", type="string", length=36,
     *   nullable=false,
     *   options={"comment"="user or group id in keycloak"})
     *
     * @Groups({"Folder:Update"})
     * @Groups({"Folder:Create"})
     * @Groups({"Media"})
     *
     * @ApiProperty(readableLink=true, readable=true)
     */
    private $target;

    /**
     * @var \App\Entity\Folder\Folder|null
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Folder\Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id")
     * @Groups({"Questionnaire:Read"})
     * @Groups({"Media"})
     *
     * @ApiProperty(readableLink=false, readable=true)
     */
    private ?Folder $folder = null;


    /**
     * @param \App\Entity\Media\MediaOwner $owner
     *
     * @return $this
     */
    public function addOwner(MediaOwner $owner): self
    {
        if (!$this->getOwners()->contains($owner)) {
            $this->getOwners()->add($owner);
            $owner->setMedia($this);
        }

        return $this;
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwners(): Collection
    {
        $this->owners = $this->owners ?? new ArrayCollection;

        return $this->owners;
    }

    /**
     * @return \App\Entity\Question\Question|null
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
    public function setQuestion(?Question $question): UserMedia
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return UserMedia
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @param \App\Entity\Media\MediaOwner $owner
     *
     * @return $this
     */
    public function removeOwner(MediaOwner $owner): self
    {
        if ($this->owners->contains($owner)) {
            $this->owners->removeElement($owner);
            // set the owning side to null (unless already changed)
            if ($owner->getMedia() === $this) {
                $owner->setMedia(null);
            }
        }

        return $this;
    }
}
