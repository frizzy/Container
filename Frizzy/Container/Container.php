<?php

/**
 * This file is part of frizzy/Container.
 *
 * @author  Bernard van Niekerk <frizzy@paperjaw.com>
 * @link    https://github.com/frizzy/Container
 * @license https://paperjaw.com/license
 * @package frizzy/Container
 *
 * (c) 2013 Bernard van Niekerk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Frizzy\Container;

use Frizzy\Map\Map;
use SplObjectStorage;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Container
 *
 * A simple Dependency Injection Container
 */
class Container extends Map implements ContainerInterface
{
    private $shared;
    private $protected;
    private $extensions;

    /**
     * Share
     *
     * {@inheritDoc}
     */
    public function share($key, $item)
    {
        if (! self::isInvokable($item)) {
            throw new UnexpectedValueException(
                'Can only share invokalble objects or closures'
            );
        }
        $this->set($key, $item);
        $this->getShared()->attach($item);
    }

    /**
     * Protected
     *
     * {@inheritDoc}
     */
    public function protect($key, $item)
    {
        if (! self::isInvokable($item)) {
            throw new UnexpectedValueException(
                'Can only protect invokalble objects or closures'
            );
        }
        $this->set($key, $item);
        $this->getProtected()->attach($item);
    }   

    /**
     * Extend
     *
     * {@inheritDoc}
     */
    public function extend($key, $extension)
    {
        if (! $this->has($key)) {
            throw new InvalidArgumentException(sprintf(
                'No item "%s" available',
                $key
            ));
        }
        $factory = parent::get($key);
        if (! self::isInvokable($factory)) {
            throw new UnexpectedValueException(sprintf(
                'Item "%s" is not a closure or invokable object and cannot be extended',
                $key
            ));
        }
        if ($this->getProtected()->contains($factory)) {
            throw new UnexpectedValueException(sprintf(
                'Protected item "%s" cannot be extended',
                $key
            ));
        }
        if (! self::isInvokable($extension)) {
            throw new UnexpectedValueException(sprintf(
                'The extension is not a closure or invokable object'
            ));
        }
        if (! $this->getExtensions()->has($key)) {
            $this->getExtensions()->set($key, new SplObjectStorage);
        }
        $this->getExtensions()->get($key)->attach($extension);
    }

    /**
     * Get
     *
     * {@inheritDoc}
     */
    public function get($key)
    {
        $item = parent::get($key);
        if (! self::isInvokable($item) || $this->getProtected()->contains($item)) {
            return $item;
        }
        $factory = $item;
        $item    = $factory->__invoke($this);
        if ($this->getShared()->contains($factory)) {
            parent::set($key, $item);
        }
        if ($this->getExtensions()->has($key)) {
            foreach ($this->getExtensions()->get($key) as $extend) {
                $extend->__invoke($this, $item);
            }
        }

        return $item;
    }
    
    /**
     * Remove
     *
     * {@inheritDoc}
     */
    public function remove($key)
    {
        $item = parent::get($key);
        parent::remove($key);
        if (! self::isInvokable($item)) {
            return;
        }
        $this->getExtensions()->remove($key);
        $this->getShared()->detach($item);
        $this->getProtected()->detach($item);   
    }

    /**
     * Get extensios
     *
     * @return Map Extensions
     */
    private function getExtensions()
    {
        if (is_null($this->extensions)) {
            $this->extensions = new Map;
        }

        return $this->extensions;
    }

    /**
     * Get shared
     *
     * @return SplObjectStore Protected items
     */
    private function getShared()
    {
        if (is_null($this->shared)) {
            $this->shared = new SplObjectStorage;
        }

        return $this->shared;
    }

    /**
     * Get protected
     *
     * @return SplObjectStore Protected items
     */
    private function getProtected()
    {
        if (is_null($this->protected)) {
            $this->protected = new SplObjectStorage;
        }

        return $this->protected;
    }

    /**
     * Is invokable
     *
     * @param mixed $object Object
     *
     * @param boolean Is invokable
     */
    private static function isInvokable($object)
    {
        return is_object($object) && method_exists($object, '__invoke');
    }
}
