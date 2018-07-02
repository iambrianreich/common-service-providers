<?php

namespace RWC\ServiceProviders\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use RWC\ServiceProviders\InvalidConfigurationException;
use RWC\ServiceProviders\MissingDependencyException;
use RWC\ServiceProviders\Urls;

class UrlsTest extends TestCase
{
    public function testThrowsExceptionIfNoConfig()
    {
        $container = new Container();
        $provider = new Urls();
        $container->register($provider);
        $this->expectException(MissingDependencyException::class);
        $container['urls']('scratch');
    }

    public function testThrowsExceptionIfNoPath()
    {
        $container = new Container();
        $container['config'] = function ($container) {
            return [];
        };
        $provider = new Urls();
        $container->register($provider);
        $this->expectException(InvalidConfigurationException::class);
        $container['urls']('scratch');
    }

    public function testThrowsExceptionIfPathDoesNotExist()
    {
        $container = new Container();
        $container['config'] = function ($container) {
            return [
                'urls' => [
                    'api' => 'https://api.org/v1'
                ]
            ];
        };
        $provider = new Urls();
        $container->register($provider);
        $this->expectException(InvalidConfigurationException::class);
        $container['urls']('scatch');
    }

    public function testReturnsCorrectPath()
    {
        $apiV1 = 'https://api.org/v1';
        $apiV2 = 'https://api.org/v2';

        $container = new Container();
        $container['config'] = function ($container) use ($apiV1, $apiV2) {
            return [
                'urls' => [
                    'apiV1' => $apiV1,
                    'apiV2' => $apiV2
                ]
            ];
        };

        $provider = new Urls();
        $container->register($provider);

        $this->assertEquals($apiV1, $container['urls']('apiV1'));
        $this->assertEquals($apiV2, $container['urls']('apiV2'));
    }
}
