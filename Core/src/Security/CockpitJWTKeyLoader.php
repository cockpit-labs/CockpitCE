<?php
/*
 * Core
 * CockpitJWTKeyLoader.php
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

namespace App\Security;

use App\Service\ApplicationGlobals;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\AbstractKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyDumperInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;

class CockpitJWTKeyLoader extends AbstractKeyLoader implements KeyDumperInterface
{
    private RawKeyLoader $jwtKeyLoakder;

    public function __construct(
        ApplicationGlobals $globals,
        EntityManagerInterface $entityManager,
        RawKeyLoader $jwtKeyLoakder
    ) {
        $this->globals = $globals;
        $this->globals->setEntityManager($entityManager);
        $this->jwtKeyLoakder = $jwtKeyLoakder;
    }

    public function dumpKey()
    {
        return $this->jwtKeyLoakder->dumpKey();
    }

    public function loadKey($type)
    {
        return $this->globals->getKeycloakKey($type);
    }
}
