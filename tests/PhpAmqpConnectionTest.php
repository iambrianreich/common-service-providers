<?php

namespace RWC\ServiceProviders\Tests;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\InvalidConfigurationException;
use RWC\ServiceProviders\MissingDependencyException;
use RWC\ServiceProviders\PhpAmqpConnection;
use RWC\ServiceProviders\ProviderCreationException;
use RWC\ServiceProviders\SendGrid;

/**
 * Class SendGridTest
 * @package RWC\ServiceProviders\Tests
 */
class PhpAmqpConnectionTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var PhpAmqpConnection
     */
    protected $serviceProvider;

    public function setUp()
    {
        $this->container = new Container();
        $this->serviceProvider = new PhpAmqpConnection();
        $this->serviceProvider->register($this->container);
    }

    public function testProvidesAmqpService()
    {
        $factory = $this->container['amqp'];
        $this->assertTrue(is_callable($factory));
    }

    public function testProvidesRabbitMQService()
    {
        $factory = $this->container['rabbitmq'];
        $this->assertTrue(is_callable($factory));
    }

    public function testProvidesAbstactConnectionService()
    {
        $factory = $this->container[AbstractConnection::class];
        $this->assertTrue(is_callable($factory));
    }

    public function testThrowsExceptionForMissingConfiguration()
    {
        $this->expectException(MissingDependencyException::class);
        $this->container['amqp']();
    }

    public function testThrowsExceptionForMissingAmqpConfiguration()
    {
        $this->container['config'] = [];
        $this->expectException(InvalidConfigurationException::class);
        $this->container['amqp']();
    }

    public function testThrowsExceptionForInvalidType()
    {
        $this->container['config'] = [
            'amqp' => [
                'type' => 'FooConnection'
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $factory = $this->container['amqp']();
    }

    public function testAmqpStreamConnectionThrowsExceptionWhenMissingHost()
    {
        $this->container['config'] = [
            'amqp' => [
                'type' => 'AMQPStreamConnection',
                //'host' => 'localhost',
                'user' => 'user',
                'password' => 'password'
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->container['amqp']();
    }

    public function testAmqpStreamConnectionThrowsExceptionWhenMissingUser()
    {
        $this->container['config'] = [
            'amqp' => [
                'type' => 'AMQPStreamConnection',
                'host' => 'localhost',
                //'user' => 'user',
                'password' => 'password'
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->container['amqp']();
    }

    public function testAmqpStreamConnectionThrowsExceptionWhenMissingPassword()
    {
        $this->container['config'] = [
            'amqp' => [
                'type' => 'AMQPStreamConnection',
                'host' => 'localhost',
                'user' => 'user',
                //'password' => 'password'
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->container['amqp']();
    }

    public function testAmqpStreamConnectionThrowsExceptionWhenConnectionFails()
    {
        $this->container['config'] = [
            'amqp' => [
                'type' => 'AMQPStreamConnection',
                'host' => 'invalid.host.name',
                'user' => 'user',
                'password' => 'password'
            ]
        ];

        $this->expectException(ProviderCreationException::class);
        $this->container['amqp']();
    }
}