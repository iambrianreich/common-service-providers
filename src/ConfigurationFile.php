<?php
declare(strict_types=1);
/**
 * Contains RWC\ServiceProviders\ConfigurationFile class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright Copyright (C) 2018 Catalyst Fabric Solutions
 */

namespace RWC\ServiceProviders;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Provides application configuration.
 *
 * @package Catalyst\Messaging\Resources
 */
class ConfigurationFile implements ServiceProviderInterface
{
    /**
     * The path to the configuration file.
     *
     * @var string
     */
    protected $filename;

    /**
     * ConfigurationProvider constructor.
     *
     * @param string $filename The configuration file.
     */
    public function __construct(string $filename)
    {
        $this->setFilename($filename);
    }

    /**
     * Sets the path to the configuration file.
     *
     * @param string $filename The path to the configuration file.
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Returns the path to the configuration file.
     *
     * @return string Returns the pateh to the configuration file.
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * Registers the resource on the container.
     *
     * @param Container $pimple The container.
     */
    public function register(Container $pimple)
    {
        // Register the resource.
        $pimple['config'] = function () {
            return $this->getConfiguration($this->getFilename());
        };
    }

    /**
     * Reads the configuration file and returns the application configuration.
     *
     * @param string $filename The path to the configuration file.
     * @return array Returns the configuration array.
     * @throws InvalidConfigurationException if the configuration file cannot be read or is invalid.
     */
    public function getConfiguration(string $filename) : array
    {
        /** @noinspection PhpIncludeInspection */
        $config = @include($filename);

        // Make sure it could be accessed.
        if ($config === false) {
            throw new InvalidConfigurationException(
                "Could not include configuration file $filename"
            );
        }

        if (! is_array($config)) {
            throw new InvalidConfigurationException(
                "Configuration file $filename did not return an array."
            );
        }

        return $config;
    }
}
