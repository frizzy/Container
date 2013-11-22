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

/**
 * ContainerTest
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetService()
    {
        $container = new Container;
        $container->share(
            'sharedService',
            function ($container) {
                return new \stdClass;
            }
        );
        $container->extend('sharedService', function ($container) {
            $container->get('sharedService')->member = 'value';
        });
        $this->assertInstanceOf('stdClass', $container['sharedService']);
        $this->assertSame($container['sharedService'], $container->get('sharedService'));

        $this->assertObjectHasAttribute('member', $container['sharedService']);
        $this->assertEquals($container['sharedService']->member, 'value');
    }
    
   
    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage Item "sharedService" is not a closure or invokable object and cannot be extended 
     */
    public function testExtendSharedServiceAfterConstruct()
    {
        $container = new Container;
        $container->share(
            'sharedService',
            function ($container) {
                return new \stdClass;
            }
        );
        
        $service = $container->get('sharedService');
        
        $container->extend('sharedService', function ($container) {
            $container->get('sharedService')->member = 'value';
        });
    }
   
    public function testFactory()
    {
        $container = new Container;
        $container['factoryService'] = function ($container) {
            return new \stdClass;
        };
        $this->assertInstanceOf('\\stdClass', $container['factoryService']);
        $this->assertNotSame($container['factoryService'], $container['factoryService']);
    }

    public function testFactoryWithExtensions()
    {
        $container = new Container;
        $container['factoryService'] = function ($container) {
            return new \stdClass;
        };
        $container->extend('factoryService', function ($container, $service) {
            $service->member = 'value';
        });
        $this->objectHasAttribute($container->get('factoryService'), 'member');   
    }
    
    public function testSetParameter()
    {
        $container = new Container;
        $testValue = 'testValue';
        $container->set('value', $testValue);
        $this->assertEquals($testValue, $container->get('value'));
    }
    
    
    public function testProtected()
    {
        $container = new Container;
        $container->protect(
            'protected',
            function ($value) {
                return $value;
            }
        );                
        $this->assertTrue(
            is_object($container['protected']) && method_exists($container['protected'], '__invoke'),
            'Item is not invokable'
        );

        $this->assertEquals($container['protected']->__invoke('value1'), 'value1');
        $this->assertEquals($container['protected']->__invoke('value2'), 'value2');
        
        return $container;
    }
    
     /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage cannot be extended
     * @depends                  testProtected
     * 
     */
    public function testExtendProtectedItem($container)
    {
        $container->extend('protected', function ($container, $service) {
            $clone = clone $service;
        });
    }
    
    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage Item "nonInvokable" is not a closure or invokable object
     */
    public function testExtendNonInvokableItem()
    {
        $container = new Container;
        $container->set('nonInvokable', array('one', 'two'));
        $container->extend('nonInvokable', function ($container, $service) {
            $clone = clone $service;
        });
    }
    
    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage The extension is not a closure or invokable object
     */
    public function testExtendWithNonInvokableItem()
    {
        $container = new Container;
        $container->set('item', function () { return new \stdClass; });
        $container->extend('item', array('1', 2));
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No item "someKey" available
     */
    public function testExtendNonExistentItem()
    {
        $container = new Container;
        $container->extend('someKey', function ($container, $service) {
            $clone = clone $service;
        });        
    }
    
}
