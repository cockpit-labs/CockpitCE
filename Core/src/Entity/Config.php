<?php
/*
 * Core
 * Config.php
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


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Config
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="Config")
 *
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\Column(name="client", type="string", unique=true)
     * @Groups({"Config:Read"})
     * @Groups({"Config:Update"})
     *
     */
    private string $client;

    /**
     * @ORM\Column(name="params", type="json", length=255, nullable=false)
     * @Groups({"Config:Read"})
     * @Groups({"Config:Update"})
     */
    private array $params;

    /**
     * @var array
     * @ORM\Column(name="keycloak", type="json", length=255, nullable=false)
     * @Groups({"Config:Read"})
     * @Groups({"Config:Update"})
     */
    private array $keycloak = [];

    /**
     * @return string|null
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * @param string $client
     *
     * @return $this
     */
    public function setClient(string $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return Config
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getKeycloak(): array
    {
        return $this->keycloak;
    }

    /**
     * @param array $keycloak
     *
     * @return Config
     */
    public function setKeycloak(array $keycloak): self
    {
        $this->keycloak = $keycloak;
        return $this;
    }

}
