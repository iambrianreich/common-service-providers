<?php
/**
 * This file contains the RWC\ServiceProviders\PhpAmqpConnection class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright 2018 Reich Web Consulting
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace RWC\ServiceProviders;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * A Service Provider for PHP AMQP Connections.
 *
 * Registers the following services: amqp, AbstractConnection, rabbitmq.
 *
 * The registered services will all resolve to a factory method for creating
 * an AbstractConnection using the registered "config" service.
 *
 * The "config" service must provide a configuration array under "amqp" or
 * "rabbitmq". If it does not an Exception is thrown. The configuration is then
 * analyzed to determine the type of connection to build based on the "type"
 * option (default is "AMQPStreamConnection". If the type is not supported an
 * Exception is thrown.
 *
 * The remainder of the configuration options are mixed with sane defaults
 * for the connection type and used to instantiate a connection. If the
 * configuration does not provide all of the required values for the
 * connection type, then an Excpetion is thrown.
 *
 * @package RWC\ServiceProvidersA
 */
class PhpAmqpConnection implements ServiceProviderInterface
{
    /**
     * Registers the following services: amqp, AbstractConnection, rabbitmq.
     *
     * The registered services will all resolve to a factory method for creating
     * an AbstractConnection using the registered "config" service.
     *
     * @param Container $pimple The container.
     */
    public function register(Container $pimple)
    {
        // Register the resource. Note that the resource is factory function
        // and that the connection is not created directly.
        $callback = function (Container $resources) {
            return function () use ($resources) {
                return $this->getAmqpConnection($resources);
            };
        };

        $pimple['amqp'] = $callback;
        $pimple[AbstractConnection::class] = $callback;
        $pimple['rabbitmq'] = $callback;
    }

    /**
     * Returns an AbstractConnection.
     *
     * The getAmqpConnection() method first extracts the single required
     * resource from the Container: the "config" service, which must provide
     * a configuration array under "amqp" or "rabbitmq". If it does not an
     * Exception is thrown. The configuration is then analyzed to determine
     * the type of connection to build based on the "type" option (default is
     * "AMQPStreamConnection". If the type is not supported an Exception is
     * thrown.
     *
     * The remainder of the configuration options are mixed with sane defaults
     * for the connection type and used to instantiate a connection. If the
     * configuration does not provide all of the required values for the
     * connection type, then an Excpetion is thrown.
     *
     * @param Container $resources The Resource container.
     * @return AbstractConnection Returns a connection instance.
     * @throws ServiceProviderException if connection cannot be created from config.
     */
    private function getAmqpConnection(Container $resources) : AbstractConnection
    {
        $config = $this->getConfiguration($resources);

        $type = $config['type'] ?? 'AMQPStreamConnection';

        switch ($type) {
            case 'AMQPStreamConnection':
                return $this->getAmqpStreamConnection($config);
            default:
                throw new InvalidConfigurationException(
                    "Unsupported connection type \"$type\""
                );
        }
    }

    /**
     * Returns the sendgrid configuration from the Container.
     *
     * The getConfiguration() method retrieves the "config" service from the
     * Container, which should return an array of configuration data. The
     * configuration must specify a "sendgrid" configuration. The sendgrid
     * configuration must be either a string, specifying the API Key, or an
     * array containing an "apiKey" index which provides the API Key, and
     * optionally an "options" array which specifies a set of options to pass
     * to the SendGrid client.
     *
     * @param Container $resources The Container providing services.
     * @return array Returns an array of normalized configuration data.
     * @throws ServiceProviderException if the configuration is invalid.
     */
    public function getConfiguration(Container $resources) : array
    {
        // Make sure there is a config available.
        if (! $resources->offsetExists('config')) {
            throw new MissingDependencyException(
                'Required "config" service is not available.'
            );
        }

        $appConfig = $resources['config'];
        $config    = $appConfig['amqp'] ?? $appConfig['rabbitmq'] ?? [];

        // Make sure there is a queue configuration
        if (empty($config)) {
            throw new InvalidConfigurationException(
                'Configuration "amqp" or "rabbitmq" not found.'
            );
        }

        return $config;
    }

    /**
     * Creates and returns an QMQPStreamConnection.
     *
     * The AMQPStreamConnection configuration from the supplied array will be
     * mixed with sane defaults derived from the AMQPStreamConnection
     * constructor. They are then used to create a new instance of the class
     * and return it.
     *
     * @param array $config The AMQPStreamConnection parameters.
     * @return AMQPStreamConnection Returns an AMQPStreamConnection
     * @throws InvalidConfigurationException if the configuration is invalid.
     * @throws ProviderCreationException if the connection fails.
     */
    private function getAmqpStreamConnection(array $config) : AMQPStreamConnection
    {
        // Mix-in defaults.
        $config = $this->getAmqpStreamConnectionConfig($config);

        try {
            return new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['password'],
                $config['vhost'],
                $config['insist'],
                $config['loginMethod'],
                $config['loginResponse'],
                $config['locale'],
                $config['timeout'],
                $config['readWriteTimeout'],
                $config['context'],
                $config['keepAlive'],
                $config['heartBeat']
            );
        } catch (\ErrorException $exception) {
            throw new ProviderCreationException(
                'Failed to connect to AMQP. See the inner exception ' .
                'for details.',
                0,
                $exception
            );
        }
    }

    /**
     * Creates and returns a configuration for an AMQPStreamConnection by
     * merging the configuration array with sane defaults derived from
     * AMQPStreamConnection's constructor.
     *
     * @param array $config The original configuration array.
     * @return array Returns the configuration array with defaults applied.
     * @throws InvalidConfigurationException if the configuration is invalid.
     */
    private function getAmqpStreamConnectionConfig(array $config) : array
    {
        $config = array_merge([
            'host'              => null,
            'port'              => 5672,
            'user'              => null,
            'password'          => null,
            'vhost'             => '/',
            'insist'            => false,
            'loginMethod'       => 'AMQPLAIN',
            'loginResponse'     => null,
            'locale'            => 'en_US',
            'timeout'           => 3.0,
            'readWriteTimeout'  => 3.0,
            'context'           => null,
            'keepAlive'         => false,
            'heartBeat'         => 0
        ], $config);

        if (! isset($config['host'])) {
            throw new InvalidConfigurationException(
                'Required configuration amqp\host not found.'
            );
        }

        if (! isset($config['user'])) {
            throw new InvalidConfigurationException(
                'Required configuration amqp\user not found.'
            );
        }

        if (! isset($config['password'])) {
            throw new InvalidConfigurationException(
                'Required configuration amqp\password not found.'
            );
        }

        return $config;
    }
}
