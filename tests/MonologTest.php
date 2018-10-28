<?php

namespace RWC\ServiceProviders\Tests;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\InvalidConfigurationException;
use RWC\ServiceProviders\MissingDependencyException;
use RWC\ServiceProviders\Monolog;
use RWC\ServiceProviders\Pdo;
use Swift_Mailer;

class MonologTest extends TestCase
{
    protected $container;
    protected $serviceProvider;
    /**
     * @var Monolog
     */
    protected $provider;

    public function setUp()
    {
        $this->container = new Container();
        $this->container['config'] = [];

        $this->provider = new Monolog();
        $this->provider->register($this->container);
    }

    public function testErrorLogProviderThrowsExceptionIfNoConfiguration()
    {
        $this->expectException(MissingDependencyException::class);
        $container = new Container();
        $provider = new Monolog();
        $provider->register($container);
        $container['logger'];
    }

    public function testErrorLogProviderReturnsMonologForLoggerResourceName()
    {
        $provider = new Monolog();
        $provider->register($this->container);
        $this->assertInstanceOf(Logger::class, $this->container['logger']);
    }

    public function testErrorLogProviderReturnsMonologForLogResourceName()
    {
        $provider = new Monolog();
        $provider->register($this->container);
        $this->assertInstanceOf(Logger::class, $this->container['log']);
    }

    public function testErrorLogProviderReturnsMonologForMonologResourceName()
    {
        $provider = new Monolog();
        $provider->register($this->container);
        $this->assertInstanceOf(Logger::class, $this->container['monolog']);
    }

    public function testThrowsExceptionWhenHandlerTypeNotSpecified()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [ ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        /**
         * @var $logger Logger
         */
        $logger = $this->container['logger'];
    }

    public function testCreatesErrorLogHandler()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [ 'type' => 'ErrorLogHandler' ]
                ]
            ]
        ];

        /**
         * @var $logger Logger
         */
        $logger = $this->container['logger'];

        $this->assertInstanceOf(Logger::class, $logger);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceof(ErrorLogHandler::class, $handlers[0]);
    }

    public function testStreamHandlerThrowsExceptionIfNoResourceSpecified()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'StreamHandler']
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        /**
         * @var $logger Logger
         */
        $logger = $this->container['logger'];
    }

    public function testStreamHandler()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'StreamHandler',
                        'resource' => 'php://output'
                    ]
                ]
            ]
        ];


        /**
         * @var $logger Logger
         */
        $logger = $this->container['logger'];

        $this->assertInstanceOf(Logger::class, $logger);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceof(StreamHandler::class, $handlers[0]);
    }

    public function testStreamHandlerThrowsExceptionForBadResource()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'StreamHandler',
                        'resource' => new \stdClass()
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandlerThrowsExceptionIfApiUserIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        //'apiUser' => 'rwc',
                        'apiKey' => 'fdsfdsfsd',
                        'from' => 'noreply@phpunit.com',
                        'to' => 'errors@phpunit.com',
                        'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandlerThrowsExceptionIfApiKeyIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        'apiUser' => 'rwc',
                        //'apiKey' => 'fdsfdsfsd',
                        'from' => 'noreply@phpunit.com',
                        'to' => 'errors@phpunit.com',
                        'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandlerThrowsExceptionIfFromIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        'apiUser' => 'rwc',
                        'apiKey' => 'fdsfdsfsd',
                        //'from' => 'noreply@phpunit.com',
                        'to' => 'errors@phpunit.com',
                        'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandlerThrowsExceptionIfToIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        'apiUser' => 'rwc',
                        'apiKey' => 'fdsfdsfsd',
                        'from' => 'noreply@phpunit.com',
                        //'to' => 'errors@phpunit.com',
                        'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandlerThrowsExceptionIfSubjectIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        'apiUser' => 'rwc',
                        'apiKey' => 'fdsfdsfsd',
                        'from' => 'noreply@phpunit.com',
                        'to' => 'errors@phpunit.com',
                        //'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSendGridHandler()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SendGridHandler',
                        'apiUser' => 'rwc',
                        'apiKey' => 'fdsfdsfsd',
                        'from' => 'noreply@phpunit.com',
                        'to' => 'errors@phpunit.com',
                        'subject' => 'this is the subject'
                    ]
                ]
            ]
        ];

        $logger = $this->container['logger'];

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceof(SendGridHandler::class, $handlers[0]);
    }

    public function testProviderThrowsExceptionIfHandlerNotSupported()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'CrapHandler'
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSwiftMailHandlerThrowsExceptionIfMailerIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SwiftMailHandler',
                        'message' => new \Swift_Message()
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSwiftMailHandlerThrowsExceptionIfMessageIsMissing()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SwiftMailHandler',
                        'mailer' => new Swift_Mailer(new \Swift_SmtpTransport()),
                        //'message' => new \Swift_Message()
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidConfigurationException::class);
        $logger = $this->container['logger'];
    }

    public function testSwiftMailHandler()
    {
        $this->container['config'] = [
            'monolog' => [
                'handlers' => [
                    [
                        'type' => 'SwiftMailHandler',
                        'mailer' => new Swift_Mailer(new \Swift_SmtpTransport()),
                        'message' => new \Swift_Message()
                    ]
                ]
            ]
        ];

        /** @var Logger $logger */
        $logger = $this->container['logger'];
        $handlers = $logger->getHandlers();
        static::assertCount(1, $handlers);
        static::assertInstanceOf(SwiftMailerHandler::class, $handlers[0]);
    }
}
