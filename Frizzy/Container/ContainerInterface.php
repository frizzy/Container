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

use Frizzy\Map\MapInterface;

/**
 * ContainerInterface
 */
interface ContainerInterface extends MapInterface
{
    /**
     * Share item
     *
     * @param scalar   $key  Key
     * @param callable $item Item
     */
    public function share($key, $item);

    /**
     * Protect item
     *
     * @param scalar   $key  Key
     * @param callable $item Item
     */
    public function protect($key, $item);

    /**
     * Extend
     *
     * @param scalar   $key       Key
     * @param callable $extension Extension
     */
    public function extend($key, $extension);
}
