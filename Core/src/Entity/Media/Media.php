<?php
/*
 * Core
 * Media.php
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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\resourceableEntity;
use App\Traits\traceableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Media
 *
 * @ORM\Table(
 *     name="Medias",
 *     indexes={
 *          @ORM\Index(name="Mediadeleted_idx", columns={"deleted_at"}),
 *          @ORM\Index(name="type_idx", columns={"discr"})
 *     }
 * )
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=4)
 * @ORM\DiscriminatorMap({
 *     "User"="UserMedia",
 *     "Tpl"="MediaTpl",
 *     "QPdf"="QuestionnairePDFMedia"
 * })
 *
 *
 * @Vich\Uploadable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 *
 * @ApiFilter(DateFilter::class, properties={"updatedAt"})
 * @ApiFilter(SearchFilter::class, properties={"owners.owner": "exact", "createdBy": "exact", "createdAt": "exact"})
 */
abstract class Media
{
    /**
     * add group (Timestamp and Blame) for TimestampableEntity and BlameableEntity
     */
    use traceableEntity;

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
     * Hook SoftDeleteable behavior
     * updates deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * Hook blameable behavior
     * updates createdBy, updatedBy fields
     */
    use BlameableEntity;

    /**
     * @var string|null
     *
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"Media"})
     */
    public $mediaUrl;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({"Media"})
     * @Groups({"Folder:Read"})
     */
    public $id;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private $fileName;

    /**
     * @var int|null
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private $size;

    /**
     * @var int
     *
     * @Groups({"Media"})
     */
    private int $width = 0;

    /**
     * @var int
     *
     * @Groups({"Media"})
     */
    private int $height = 0;

    /**
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private $dimensions;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private $mimeType;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private $originalName;

    /**
     * @var File|null
     *
     * @Assert\NotNull(groups={"media_object_create"})
     * @Vich\UploadableField(mapping="media_object", fileNameProperty="fileName", size="size", mimeType="mimeType",
     *                                               originalName="originalName", dimensions="dimensions")
     */
    private $file;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     * @Groups({"Media"})
     */
    private string $pathName;

    /**
     * @return string|null
     */
    public function getDimensions(): ?string
    {
        $this->extractDimensions();
        return $this->dimensions;
    }

    /**
     * @param array|null $dimensions
     *
     * @return $this
     */
    public function setDimensions(?array $dimensions): self
    {
        $this->extractDimensions($dimensions);

        $this->dimensions = json_encode($dimensions);

        return $this;
    }

    /**
     * @param array|null $dimensions
     *
     * @return $this
     */
    private function extractDimensions(?array $dimensions=null): self
    {
        if($dimensions==null){
            $dimensions=json_decode($this->dimensions, true);
        }
        if(!empty($dimensions) && count($dimensions)==2){
            $this->setWidth(intval($dimensions[0]));
            $this->setHeight(intval($dimensions[1]));
        }
        return $this;
    }
    /**
     * @return \Symfony\Component\HttpFoundation\File\File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     *
     * @return $this
     */
    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        if($this->height==0){
            $this->extractDimensions();
        }
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return Media
     */
    public function setHeight(int $height): Media
    {
        $this->height = $height;
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
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimeType
     *
     * @return $this
     */
    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * @param string|null $originalName
     *
     * @return $this
     */
    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathName(): string
    {
        return $this->pathName;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     *
     * @return $this
     */
    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        if($this->width==0){
            $this->extractDimensions();
        }
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return Media
     */
    public function setWidth(int $width): Media
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $pathName
     *
     * @return Media
     */
    public function setPathName(string $pathName): self
    {
        $this->pathName = $pathName;
        return $this;
    }
}
