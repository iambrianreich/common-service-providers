<?php

namespace RWC\ServiceProviders\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\SendGrid;

/**
 * Class SendGridTest
 * @package RWC\ServiceProviders\Tests
 */
class SendGridTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var SendGrid
     */
    private $serviceProvider;

    public function setUp()
    {
        $this->container = new Container();
        $this->serviceProvider = new SendGrid();
    }

    public function testRegisterCreatesLowercaseSendGridKey()
    {
        $this->container->register($this->serviceProvider);
        $this->assertTrue($this->container->offsetExists('sendgrid'));
    }

    public function testRegisterCreatesClassNameSendGridKey()
    {
        $this->container->register($this->serviceProvider);
        $this->assertTrue($this->container->offsetExists(SendGrid::class));
    }

    public function testRegisterCreatedLowercaseSendGridFactoryKey()
    {
        $this->container->register($this->serviceProvider);
        $this->assertTrue($this->container->offsetExists('sendgridFactory'));
    }

    public function testRegisterCreatesClassNameFactorySendGridKey()
    {
        $this->container->register($this->serviceProvider);
        $this->assertTrue($this->container->offsetExists(SendGrid::class . 'Factory'));
    }

    /**
     * @expectedException \RWC\ServiceProviders\MissingDependencyException
     */
    public function testCallbackThrowsUnknownIdentifierExceptionIfNoConfigPresent()
    {
        $container = new Container();
        $this->assertFalse($container->offsetExists('config'));
        $container->register($this->serviceProvider);
        $sendgrid = $container['sendgrid'];
    }

    /**
     * @expectedException \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackInvalidConfigurationExceptionIfSendgridConfigurationIsMissing()
    {
        $container = new Container();
        $container['config'] = function () {
            return [];
        };

        $this->assertTrue($container->offsetExists('config'));
        $container->register($this->serviceProvider);
        $sendgrid = $container['sendgrid'];
    }

    public function testCallbackReturnsSendgridObjectWhenConfigurationIsAString()
    {
        $container = new Container();
        $container['config'] = function () {
            return [ 'sendgrid' => 'FAKE API KEY' ];
        };

        $container->register($this->serviceProvider);

        /** @var $sendgrid \SendGrid
         */
        $sendgrid = $container['sendgrid'];
        $this->assertInstanceOf(\SendGrid::class, $sendgrid);
    }

    /**
     * @expectedException \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackInvalidConfigurationExceptionIfSendgridConfigurationIsNotStringOrArrays()
    {
        $container = new Container();
        $container['config'] = function () {
            return [ 'sendgrid' => new \stdClass() ];
        };

        $this->assertTrue($container->offsetExists('config'));
        $container->register($this->serviceProvider);
        $sendgrid = $container['sendgrid'];
    }

    /**
     * @expectedException \RWC\ServiceProviders\InvalidConfigurationException
     */
    public function testCallbackInvalidConfigurationExceptionIfSendgridConfigurationHasNoApiKey()
    {
        $container = new Container();
        $container['config'] = function () {
            return [
                'sendgrid' => [
                    'fubar' => 'biz'
                ]
            ];
        };

        $this->assertTrue($container->offsetExists('config'));
        $container->register($this->serviceProvider);
        $sendgrid = $container['sendgrid'];
    }
}