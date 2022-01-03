<?php
/*
 * Core
 * CockpitSubscriber.php
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

namespace App\EventSubscriber;

namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\Media\Media;
use App\Entity\Media\MediaTpl;
use App\Entity\Media\UserMedia;
use App\Service\ApplicationGlobals;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Translatable\TranslatableListener;
use SlopeIt\ClockMock\ClockMock;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CockpitSubscriber implements EventSubscriberInterface
{
    const COOKIE_NAME          = 'fakeTime';
    const QUERY_PARAMETER_NAME = 'fakeTime';
    const HEADER_NAME          = 'X-FAKETIME';

    /**
     * @var BlameableListener
     */
    private $blameableListener;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var TranslatableListener
     */
    private $translatableListener;
    /**
     * @var LoggableListener
     */
    private $loggableListener;
    /**
     * @var SoftDeleteableListener
     */
    private $softDeleteableListener;
    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;

    public function __construct(
        BlameableListener $blameableListener,
        TokenStorageInterface $tokenStorage,
        TranslatableListener $translatableListener,
        LoggableListener $loggableListener,
        SoftDeleteableListener $softDeleteableListener,
        ParameterBagInterface $params,
        ApplicationGlobals $globals
    ) {
        $this->blameableListener      = $blameableListener;
        $this->tokenStorage           = $tokenStorage;
        $this->translatableListener   = $translatableListener;
        $this->loggableListener       = $loggableListener;
        $this->softDeleteableListener = $softDeleteableListener;
        $this->parameters             = $params;
        $this->globals                = $globals;ClockMock::reset();
    }

    private function timecopRequest(Request $request)
    {
        $fakeTimeString = null;

        if ($request->headers->get(static::HEADER_NAME, null) !== null) {
            $fakeTimeString = $request->headers->get(static::HEADER_NAME);
        }

        $cookies = $request->cookies;
        if ($cookies->has(static::COOKIE_NAME)) {
            $fakeTimeString = $cookies->get(static::COOKIE_NAME);
        }

        if ($request->get(static::QUERY_PARAMETER_NAME, null) !== null) {
            $fakeTimeString = $request->get(static::QUERY_PARAMETER_NAME);
        }
        if (!empty($fakeTimeString)) {
            $fakeTime=new \DateTime();
            $fakeTime->setTimestamp(strtotime($fakeTimeString));

            ClockMock::freeze($fakeTime);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST        => [['onKernelRequest', EventPriorities::PRE_READ]],
            KernelEvents::FINISH_REQUEST => 'onLateKernelRequest',
            KernelEvents::VIEW           => [['onPreSerialize', EventPriorities::PRE_SERIALIZE]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $this->timecopRequest($request);

        if ($this->tokenStorage !== null &&
            $this->tokenStorage->getToken() !== null &&
            $this->tokenStorage->getToken()->isAuthenticated() === true
        ) {
            $this->blameableListener->setUserValue($this->tokenStorage->getToken()->getUser());
            $this->loggableListener->setUsername($this->tokenStorage->getToken()->getUser()->getUsername());
        }
    }

    public function onLateKernelRequest(FinishRequestEvent $event): void
    {
        $this->translatableListener->setTranslatableLocale($event->getRequest()->getLocale());
    }

    public function onPreDeSerialize(RequestEvent $event): void
    {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ViewEvent $event
     */
    public function onPreSerialize(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        $request          = $event->getRequest();

        if ($controllerResult instanceof Response
            || !$request->attributes->getBoolean('_api_respond', true)) {
            return;
        }

        if (
            ($attributes = RequestAttributesExtractor::extractAttributes($request))
            && (is_a($attributes['resource_class'], Media::class, true)
                || is_a($attributes['resource_class'], UserMedia::class, true)
                || is_a($attributes['resource_class'], MediaTpl::class, true))
        ) {

            $entities = $controllerResult;

            if (!is_iterable($entities)) {
                $entities = [$entities];
            }

            foreach ($entities as $entity) {
                if (method_exists($entity, "getId") && ($entity->getId() ?? null) !== null) {
                    // set mediaUrl for media object
                    $iri              = $this->globals->getIriConverter()->getIriFromResourceClass(get_class($entity));
                    $entity->mediaUrl = $iri . '/' . $entity->getId() . '/content';
                }
            }
        }
    }

}

