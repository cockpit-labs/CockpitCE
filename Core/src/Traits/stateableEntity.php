<?php
/*
 * Core
 * stateableEntity.php
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

namespace App\Traits;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait stateableEntity
 * Add a 'state' field in entity
 *
 * @package App\Traits
 */
trait stateableEntity
{
    /**
     * @var string|null
     * @ORM\Column(name="state", type="string", length=40, nullable=false)
     * @Assert\NotBlank
     * @Groups({"State"})
     */
    private $state = 'DRAFT';

    /**
     * @param $method
     * @param $args
     *
     * @return mixed|void
     * @throws \ReflectionException
     */
    static public function __callStatic($method, $args)
    {

        if (preg_match('/^([gs]et)([A-Z])(.*)$/', $method, $match)) {
            $reflector = new \ReflectionClass(__CLASS__);
            $property  = strtolower($match[2]) . $match[3];
            if ($reflector->hasProperty($property)) {
                $property = $reflector->getProperty($property);
                switch ($match[1]) {
                    default:
                    case 'get':
                        return self::${$property->name};
                    case 'set':
                        return self::${$property->name} = $args[0];
                }
            } else {
                throw new InvalidArgumentException("Property {$property} doesn't exist");
            }
        }
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return strtoupper($this->state);
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState(string $state): self
    {
        $this->state = strtoupper($state);

        return $this;
    }

}
