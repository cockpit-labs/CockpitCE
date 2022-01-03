<?php
/*
 * Core
 * Attribute.php
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

namespace App\Entity\Attribute;

use App\Traits\descriptionableEntity;
use App\Traits\labelableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *     name="Attributes"
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *     "Group"="GroupAttribute",
 *     "User"="UserAttribute"
 * })
 *
 */
abstract class Attribute implements Translatable
{
    const ATTRIBUTETYPE_STRING = 'string';
    const ATTRIBUTETYPE_INT    = 'int';
    const ATTRIBUTETYPE_FLOAT  = 'float';
    const ATTRIBUTETYPE_ICON   = 'icon';

    /**
     * add a label field
     */
    use labelableEntity;

    /**
     * add a description field
     */
    use descriptionableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Read"})
     * @Groups({"GroupAttribute:Read"})
     * @Groups({"Group:Update"})
     * @Groups({"UserAttribute:Read"})
     *
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=false)
     *
     * @Groups({"GroupAttribute:Read"})
     * @Groups({"UserAttribute:Read"})
     * @Groups({"GroupAttribute:Update"})
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Target:Read"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"Group:Update"})
     */
    private string $type = '';

    /**
     * @var string
     * @ORM\Column(name="value", type="string", nullable=false)
     *
     * @Groups({"GroupAttribute:Read"})
     * @Groups({"UserAttribute:Read"})
     * @Groups({"GroupAttribute:Update"})
     * @Groups({"UserAttribute:Update"})
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     * @Groups({"Target:Read"})
     * @Groups({"FolderTpl:Read"})
     * @Groups({"FolderTpl:Update"})
     */
    private string $value = '';

    /**
     * @var int
     * @ORM\Column(name="position", type="integer", nullable=false, options={"default" : 1})
     *
     * @Groups({"GroupAttribute:Read"})
     * @Groups({"UserAttribute:Read"})
     * @Groups({"GroupAttribute:Update"})
     * @Groups({"UserAttribute:Update"})
     * @Groups({"Group:Read"})
     * @Groups({"Group:Read:View"})
     * @Groups({"Group:Update"})
     */
    private $position = 1;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
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
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Attribute
     */
    public function setType(string $type): Attribute
    {
        if (!in_array($type, [
            self::ATTRIBUTETYPE_FLOAT,
            self::ATTRIBUTETYPE_ICON,
            self::ATTRIBUTETYPE_INT,
            self::ATTRIBUTETYPE_STRING
        ])) {
            throw new InvalidArgumentException("Invalid type");
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Attribute
     */
    public function setValue(string $value): Attribute
    {
        $this->value = $value;
        return $this;
    }

}
