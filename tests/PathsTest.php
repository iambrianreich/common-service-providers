<?php

namespace RWC\ServiceProviders\Tests;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\ConfigurationFile;
use RWC\ServiceProviders\InvalidConfigurationException;
use RWC\ServiceProviders\MissingDependencyException;
use RWC\ServiceProviders\Monolog;
use RWC\ServiceProviders\Paths;
use RWC\ServiceProviders\Pdo;

class PathsTest extends TestCase
{
    public function testThrowsExceptionIfNoConfig()
    {
        $container = new Container();
        $provider = new Paths();
        $container->register($provider);
        $this->expectException(MissingDependencyException::class);
        $container['paths']('scratch');
    }

    public function testThrowsExceptionIfNoPath()
    {
        $container = new Container();
        $container['config'] = function ($container) {
            return [];
        };
        $provider = new Paths();
        $container->register($provider);
        $this->expectException(InvalidConfigurationException::class);
        $container['paths']('scratch');
    }

    public function testThrowsExceptionIfPathDoesNotExist()
    {
        $container = new Container();
        $container['config'] = function ($container) {
            return [
                'paths' => [
                    'scratch' => __DIR__
                ]
            ];
        };
        $provider = new Paths();
        $container->register($provider);
        $this->expectException(InvalidConfigurationException::class);
        $container['paths']('scatch');
    }

    public function testReturnsCorrectPath()
    {
        $scratch = __DIR__ . DIRECTORY_SEPARATOR . 'scratch';
        $images = __DIR__ . DIRECTORY_SEPARATOR . 'images';

        $container = new Container();
        $container['config'] = function ($container) use ($images, $scratch) {
            return [
                'paths' => [
                    'images' => $images,
                    'scratch' => $scratch
                ]
            ];
        };

        $provider = new Paths();
        $container->register($provider);

        $this->assertEquals($scratch, $container['paths']('scratch'));
        $this->assertEquals($images, $container['paths']('images'));
    }
}
