<?php
/*
 * Core
 * resourceableEntity.php
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

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait resourceableEntity
 * Add a 'resource' field in entity containing entity name
 *
 * @package App\Traits
 */
trait resourceableEntity
{

    /**
     * @var string|null
     * @Groups({"Resource"})
     */
    private $resource = '';

    /**
     * @return mixed|string
     */
    private function getRealClass()
    {
        $classname = get_class();

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    /**
     * @return string|null
     */
    public function __toString(): string
    {
        if (method_exists($this, 'getLabel')) {
            return $this->getLabel() ?? "-";
        } else {
            return "-";
        }
    }

    /**
     * @return string|null
     */
    public function getResource(): ?string
    {
        if (!empty($this->resource)) {
            return $this->resource;
        }
        $this->resource = $this->getRealClass();
        return $this->resource;
    }

    /**
     * @param string|null $resource
     */
    public function setResource(?string $resource): self
    {
        $this->resource = $resource;
        return $this;
    }

}
