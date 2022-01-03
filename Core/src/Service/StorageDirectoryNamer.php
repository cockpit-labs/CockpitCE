<?php
/*
 * Core
 * storageFileNamer.php
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


namespace App\Service;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

/**
 * Directory namer wich can create subfolder depends on current datetime.
 */
class StorageDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface
{
    /**
     * @var string
     */
    private $dateTimeFormat = 'Y/m/d';

    /**
     * @var PropertyAccessorInterface|null
     */
    private $propertyAccessor;

    /**
     * @var string|null
     */
    private $dateTimeProperty;

    /**
     * @var \App\Service\ApplicationGlobals
     */
    private ApplicationGlobals $globals;

    /**
     * StorageDirectoryNamer constructor.
     *
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface|null $propertyAccessor
     * @param \App\Service\ApplicationGlobals                                  $globals
     */
    public function __construct(?PropertyAccessorInterface $propertyAccessor, ApplicationGlobals $globals)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->globals          = $globals;
    }

    /**
     * @param array $options
     */
    public function configure(array $options): void
    {
        $options = \array_merge(['date_time_format' => $this->dateTimeFormat], $options);

        $this->dateTimeFormat = $options['date_time_format'];

        if (isset($options['date_time_property'])) {
            $this->dateTimeProperty = $options['date_time_property'];
        }
    }

    /**
     * @param object                                       $object
     * @param \Vich\UploaderBundle\Mapping\PropertyMapping $mapping
     *
     * @return string
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (empty($this->dateTimeFormat)) {
            throw new \LogicException('Option "date_time_format" is empty.');
        }
        if (null !== $this->dateTimeProperty) {
            $dateTime = $this->propertyAccessor->getValue($object, $this->dateTimeProperty)->format('U');
        } else {
            $msg = 'Not passing "date_time_property" option is deprecated and will be removed in version 2.';
            @\trigger_error($msg, E_USER_DEPRECATED);
            $dateTime = time();
        }

        return $this->globals->getFqdn() . '/' .
            \date($this->dateTimeFormat, $dateTime);
    }
}
