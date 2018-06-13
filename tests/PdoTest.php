<?php

namespace RWC\ServiceProviders\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\MissingDependencyException;
use RWC\ServiceProviders\Pdo;

class PdoTest extends TestCase
{
    protected $container;
    protected $serviceProvider;

    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\MissingDependencyException
     */
    public function testCallBackThrowsMissingDependencyExceptionIfConfigNotFound()
    {
        $container = new Container();
        $container->register(new Pdo());
        $container['pdo']();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallBackThrowsInvalidConfigurationExceptionIfDatabaseConfigurationNotFound()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [];
        };
        $container->register(new Pdo());
        $container['pdo']();
    }

    public function testCallbackWorksForConfigurationNamedDatabase()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'database' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => ''
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testCallbackWorksForConfigurationNamedPdo()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => ''
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testCallbackWorksForNamedInstances()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'shipping' => [
                        'dsn' => 'sqlite::memory:',
                        'username' => '',
                        'password' => ''
                    ]
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']('shipping');
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallBackThrowsInvalidConfigurationExceptionIfNamedInstanceRequestedAndDoesNotExist()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'shipping' => [
                        'dsn' => 'sqlite::memory:',
                        'username' => '',
                        'password' => ''
                    ]
                ]
            ];
        };
        $container->register(new Pdo());
        $container['pdo']('shlipping');
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackThrowsInvalidConfigurationExceptionIfDsnNotSet()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    //'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => ''
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackThrowsInvalidConfigurationExceptionIfUsernameNotSet()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    //'username' => '',
                    'password' => ''
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackThrowsInvalidConfigurationExceptionIfPasswordNotSet()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    //'password' => ''
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackThrowsInvalidConfigurationExceptionIfOptionsNotAnArray()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => '',
                    'options' => new \stdClass()
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
    }

    /**
     * @expectedException  \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackThrowsInvalidConfigurationExceptionIfAttributesNotAnArray()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => '',
                    'attributes' => new \stdClass()
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['pdo']();
    }

    public function testCallbackReturnsValidValueWhenCalledWithDatabase()
    {
        $container = new Container();
        $container['config'] = function ($c) {
            return [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                    'username' => '',
                    'password' => '',
                ]
            ];
        };
        $container->register(new Pdo());
        $pdo = $container['database']();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
}