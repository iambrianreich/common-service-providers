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
use RWC\ServiceProviders\Pdo;

class ConfigurationFileTest extends TestCase
{
    protected $container;
    protected $serviceProvider;
    protected $filename;
    /**
     * @var Monolog
     */
    protected $provider;

    public function setUp()
    {
        $this->container = new Container();
        $this->filename = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
        file_put_contents($this->filename, "<?php\r\nreturn [ 'key' => 'value' ];");

        $this->provider = new ConfigurationFile($this->filename);
        $this->provider->register($this->container);
    }

    public function tearDown()
    {
        unlink($this->filename);
    }

    public function testWorksForValidConfiguration()
    {
        $config = $this->container['config'];

        $this->assertTrue(is_array($config));
        $this->assertArrayHasKey('key', $config);
        $this->assertEquals($config['key'], 'value');
    }

    public function testThrowsExceptionForMissingFile()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'doesnotexist.php';
        $this->assertFalse(file_exists($file));
        $this->expectException(InvalidConfigurationException::class);
        $container = new Container();
        $container->register(new ConfigurationFile($file));
        $config = $container['config'];
    }

    public function testThrowsExceptionIsConfigurationFileDoesNotReturnArray()
    {
        file_put_contents($this->filename, "<?php\r\nreturn 'string';");
        $this->expectException(InvalidConfigurationException::class);
        $this->container['config'];
    }
}
