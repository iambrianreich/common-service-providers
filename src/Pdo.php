<?php
/**
 * This file contains the RWC\ServiceProviders\Pdo class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright 2018 Reich Web Consulting
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace RWC\ServiceProviders;

use Pimple\Container;
use Pimple\Exception\UnknownIdentifierException;
use Pimple\ServiceProviderInterface;

/** @noinspection PhpClassNamingConventionInspection */

/**
 * Provides PDO database connections.
 *
 * The Pdo ServiceProvider creates and returns PDO database connections. It is
 * dependant on the existence of a "config" service registered in the Container
 * which provides the database connection details.
 *
 * The PDO configuration may be named either "pdo" or "database". This is
 * essentially for backwards compatibility with some existing projects. The
 *
 * The configuration may then be specified in several ways. The first is to
 * define a single database configuration as key/value pairs within the
 * database configuration array.  The second is to define named connections
 * within the database configuration. Examples below.
 *
 * Each database configuration MUST specify a dsn, username, and password. A
 * connection may also supply an "options" key which should be a valid array
 * of PDO options which will pass directly to the PDO constructor. The
 * configuration may also specify an array containing key/value pairs of
 * PDO attributes, which will be passed directly to PDO::setAttribute() on the
 * created connection object.
 *
 * // Single connection
 * [
 *     'pdo' => [
 *         'dsn' => '...',
 *         'username' => '...',
 *         'password' => '...'
 *     ]
 *  ]
 *  // Retrieved with
 *  $container['pdo']();
 *
 * // Named connections
 * [
 *     'pdo' => [
 *         'shipping' => [
 *             'dsn' => '...',
 *             'username' => '...',
 *             'password' => '...'
 *         ]
 *     ]
 * ]
 *
 * @package RWC\ServiceProviders
 */
class Pdo implements ServiceProviderInterface
{
    /**
     * @param Container $resources
     * @param string|null $name The optional database resource name.
     * @return array
     * @throws ServiceProviderException
     */
    public function getConfiguration(Container $resources, ?string $name = null) : array
    {
        try {
            // Make sure we have a config resource.
            $config = $resources->offsetGet('config');
        } catch (UnknownIdentifierException $configNotFound) {
            throw new MissingDependencyException(
                "Container did not provide dependent \"config\" service."
            );
        }

        // We can name our config pdo or database.
        if (isset($config['pdo'])) {
            $config = $config['pdo'];
        } elseif (isset($config['database'])) {
            $config = $config['database'];
        } else {
            $config = null;
        }

        // Make sure the config specifies one of our two valid configuration names.
        if (is_null($config)) {
            throw new InvalidConfigurationException(
                'Required config "pdo" or "database" not found.'
            );
        }

        // Are we dealing with a named database instance?
        if (! is_null($name)) {
            $config = $config[$name] ?? null;

            // Make sure the named instance exists.
            if (empty($config)) {
                throw new InvalidConfigurationException(
                    "Named PDO resource \"$name\" requested but " .
                    "a matching configuration was not found."
                );
            }
        }

        // Fallbacks for username because consistency is a bitch.
        $config['username'] = $config['username'] ?? $config['user'] ?? null;

        // Mix-in defaults.
        $config = array_merge([
            'dsn' => null,
            'username' => null,
            'password' => null,
            'options' => [],
            'attributes' => [
                // Force Exception-based errors by default.
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        ], $config);

        // Make sure we have all requied items
        if (! is_string($config['dsn'])) {
            throw new InvalidConfigurationException('dsn config not present');
        }

        if (! is_string($config['username'])) {
            throw new InvalidConfigurationException('username config not present');
        }

        if (! is_string($config['password'])) {
            throw new InvalidConfigurationException('password config not present');
        }

        if (! is_array($config['options'])) {
            throw new InvalidConfigurationException(
                'options config is optional, but must be an array when specified'
            );
        }

        if (! is_array($config['attributes'])) {
            throw new InvalidConfigurationException(
                'attributes config is optional, but must be an array when specified'
            );
        }

        return $config;
    }

    /**
     * Registers database connection with the Container.
     *
     * The container must have a "config" value registered, which contains the
     * application configuration. The config must have a key called "database"
     * which specifies the database configuration.  The database connection
     * will be created using the configuration.
     *
     * If the database configuration is invalid, or the connection cannot be
     * established, a Exception is thrown.
     *
     * @param  Container $resources The Container
     * @return void
     */
    public function register(Container $resources)
    {
        $callback = function (?string $name = null) use ($resources) {
            $config = $this->getConfiguration($resources, $name);
            $pdoConnection    =  new \PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                $config['options']
            );

            // Process attributes
            foreach ($config['attributes'] as $attribute => $value) {
                $pdoConnection->setAttribute($attribute, $value);
            }

            return $pdoConnection;
        };

        $resources['pdo'] = function () use ($callback) {
            return $callback;
        };
        $resources['database'] =  function () use ($callback) {
            return $callback;
        };
    }
}
