<?php
/*
 * Core
 * KeycloakController.php
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

namespace App\Controller;

use App\DataProvider\CommonDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KeycloakController extends AbstractController
{
    /**
     * @var \App\DataProvider\CommonDataProvider
     */
    private $keycloakDataProvider;

    /**
     * KeycloakController constructor.
     *
     * @param \App\DataProvider\CommonDataProvider $keycloakDataProvider
     */
    public function __construct(CommonDataProvider $keycloakDataProvider)
    {
        $this->keycloakDataProvider = $keycloakDataProvider;
    }

    /**
     * @Route("/keycloak.js", name="app_keycloak_js")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function js(): Response
    {
        return new Response(file_get_contents($this->keycloakDataProvider->getKeycloakUrl() . '/auth/js/keycloak.js'));
    }
}
