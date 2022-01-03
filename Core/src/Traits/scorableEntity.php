<?php
/*
 * Core
 * scorableEntity.php
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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait scorableEntity
 * Add a 'score' field representing a score value for the entity
 *
 * @package App\Traits
 */
trait scorableEntity
{
    /**
     * @var int|null
     * @ORM\Column(name="score", type="float", nullable=true)
     * @Groups({"Score"})
     */
    private $score = null;

    /**
     * @var int|null
     * @ORM\Column(name="scoreDivider", type="integer", nullable=true)
     * @Groups({"Score"})
     */
    private $scoreDivider = null;

    /**
     * @return int|null
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * @param int $score
     *
     * @return $this
     */
    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getScoreDivider(): ?int
    {
        return $this->scoreDivider;
    }

    /**
     * @param int $scoreDivider
     *
     * @return $this
     */
    public function setScoreDivider(int $scoreDivider): self
    {
        $this->scoreDivider = $scoreDivider;

        return $this;
    }

    public function processScore(): ?self
    {
        // calcul this object score
        if (method_exists($this, 'getScoreValue')) {
            $this->getScoreValue();
        }
        // if there is child objects for this object
        if (method_exists($this, 'getChildEntities')) {
            foreach ($this->getChildEntities() as $childEntity) {
                if (method_exists($childEntity, 'getScore')
                    && method_exists($childEntity, 'getScoreDivider')
                    && method_exists($childEntity, 'processScore')) {
                    $childEntity->processScore();
                    $this->setScoreDivider($this->getScoreDivider() + $childEntity->getScoreDivider());
                    $this->setScore($this->getScore() + $childEntity->getScore());
                }
            }
        }
        return $this;
    }
}
