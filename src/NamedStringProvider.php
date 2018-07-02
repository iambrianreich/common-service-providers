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
 * The NamedStringsProvider is a service provider that acts as a generic
 * provider of strings from the service configuration. The provider is created
 * by passing a string to the constructor, which is a key that should exist in
 * the resource configuration. When references, the provider returns a function
 * that accepts a string, which is a key into the named configuration array.
 *
 * $container = new Container();
 * $provider = new NamedStringProvider('urls');
 * $container['config'] = [ 'urls' => [ 'api' => 'https://api.org/v1/' ] ];
 * $provider->register($container);
 * $url = $container['urls']('api');  // returns the api url
 *
 * @package Catalyst\Messaging\Resources
 */
class NamedStringProvider implements ServiceProviderInterface
{
    /**
     * The name of the configuration key.
     *
     * @var string
     */
    public $collectionName;

    /**
     * Returns the key used to looking the named string collection.
     *
     * @return string Returns the key used to looking the named string collection.
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * Sets the key used to looking the named string collection.
     *
     * @param string $collectionName The key used to looking the named string collection.
     */
    protected function setCollectionName(string $collectionName): void
    {
        $this->collectionName = $collectionName;
    }

    /**
     * NamedStringProvider constructor.
     *
     * @param string $collectionName
     */
    public function __construct(string $collectionName)
    {
        $this->setCollectionName($collectionName)    ;
    }

    /**
     * Registers the resource on the container.
     *
     * @param Container $pimple The container.
     */
    public function register(Container $pimple)
    {
        // Register the resource.
        $pimple[$this->getCollectionName()] = function () use ($pimple) {
            return function (string $key) use ($pimple) : string {
                // Make sure we've got a config
                if (! $pimple->offsetExists('config')) {
                    throw new MissingDependencyException(
                        'Container is missing dependent service "config"'
                    );
                }

                $config = $pimple['config'];

                $strings = $config[$this->getCollectionName()] ?? [];
                $string  = $strings[$key] ?? null;

                // Make sure path is defined.
                if (is_null($string)) {
                    throw new InvalidConfigurationException(
                        $this->getCollectionName() .
                        'list does not contain "' . $key .
                        '" definition.'
                    );
                }

                return (string) $string;
            };
        };
    }
}
