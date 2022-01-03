<?php
/*
 * Core
 * SuperuserContextBuilder.php
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


namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\CentralAdmin\KeycloakConnector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

final class SuperuserContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface
     */
    private SerializerContextBuilderInterface $decorated;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    private Security $security;

    /**
     * SuperuserContextBuilder constructor.
     *
     * @param \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface               $decorated
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        SerializerContextBuilderInterface $decorated,
        Security                          $security,
        AuthorizationCheckerInterface     $authorizationChecker
    ) {
        $this->decorated            = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->security             = $security;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param bool                                      $normalization
     * @param array|null                                $extractedAttributes
     *
     * @return array
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (!empty($this->security->getUser()) &&
            in_array(KeycloakConnector::toSymfonyRole('Superuser'), $this->security->getUser()->getRoles()) ) {
            if ($normalization) {
                $context['groups'][] = 'Superuser:Read';
            } else {
                $context['groups'][] = 'Superuser:Update';
                if ($context[$context['operation_type'] . '_operation_name'] == 'post') {
                    $context['groups'][] = 'Superuser:Create';
                }
            }
        }
        return $context;
    }
}
