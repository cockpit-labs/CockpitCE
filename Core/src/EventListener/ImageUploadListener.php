<?php
/*
 * Core
 * ImageUpload.php
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

namespace App\EventListener;

use Vich\UploaderBundle\Event\Event;

class ImageUploadListener
{
    /**
     * @param \Vich\UploaderBundle\Event\Event $event
     */
    public function onVichUploaderPreUpload(Event $event)
    {
        // no time travel on object store, so disable during uploadin
        if (!empty($_SERVER['REQUEST_TIME'])
            && function_exists('timecop_return')) {
            timecop_return();
        }
    }

    /**
     * @param \Vich\UploaderBundle\Event\Event $event
     */
    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();
        // add full path name in object
        $event->getObject()->setPathName($object->getFile()->getPathName());
        // restore time_travel
        if (!empty($_SERVER['REQUEST_TIME'])
            && function_exists('timecop_travel')) {
            timecop_travel($_SERVER['REQUEST_TIME']);
        }
    }

}
