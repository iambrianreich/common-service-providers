<?php
declare(strict_types=1);

namespace RWC\ServiceProviders;

use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Swift_Mailer;

/**
 * Provides the Logger resource.
 *
 * The logger is dependent on the existence of a QueueConnection configuration.
 * The Logger will handle messages by sending them to an AmqpHandler as well as
 * to the ErrorLogHandler which puts logs into the system's default logging
 * mechanism.
 *
 * @package PF\Resources
 */
class Monolog implements ServiceProviderInterface
{
    /**
     * Registers the logger resource.
     *
     * @param  Container $pimple The Container.
     * @return void
     * @throws MissingDependencyException if config is not present.
     */
    public function register(Container $pimple)
    {
        $callback = function (Container $pimple) {

            if (! $pimple->offsetExists('config')) {
                throw new MissingDependencyException(
                    'Monolog Service Provider depends on a "config" service.'
                );
            }

            $config = $pimple['config'];

            // A log by any other name...
            $loggerConfig = $config['log'] ?? $config['logger'] ?? $config['monolog'] ?? [];

            $logger =  new Logger('Monolog');
            $logger = $this->configure($logger, $loggerConfig);
            return $logger;
        };
        $pimple['log'] = $callback;
        $pimple['logger'] = $callback;
        $pimple['monolog'] = $callback;
    }

    /**
     * @param Logger $logger
     * @param array $config
     * @return Logger Returns the configured logger.
     * @throws InvalidConfigurationException
     */
    protected function configure(Logger $logger, array $config) : Logger
    {
        $name = $config['name'] ?? 'Monolog';
        $logger = $logger->withName($name);

        $handlerConfigs = $config['handlers'] ?? [];


        foreach ($handlerConfigs as $handlerConfig) {
            // Merge handler config with default.
            $handlerConfig = array_merge(
                [
                    'level' => Logger::DEBUG,
                    'type'  => null
                ],
                $handlerConfig
            );

            $type = $handlerConfig['type'];

            // Make sure the type is specified.
            if (empty($type)) {
                throw new InvalidConfigurationException('No handler "type" specified.');
            }

            switch ($type) {
                case 'StreamHandler':
                    $this->configureStreamHandler($logger, $handlerConfig);
                    break;
                case 'ErrorLogHandler':
                    $this->configureErrorLogHandler($logger, $handlerConfig);
                    break;
                case 'SendGridHandler':
                    $this->configureSendGridHandler($logger, $handlerConfig);
                    break;
                case 'SwiftMailHandler':
                    $this->configureSwiftMailHandler($logger, $handlerConfig);
                    break;
                // Invalid handler type
                default:
                    throw new InvalidConfigurationException(
                        "Handler type \"$type\" not supported."
                    );
            }
        }

        return $logger;
    }

    /**
     * Creates and adds an ErrorLogHandler to the Logger.
     *
     * @param Logger $logger The logger
     * @param array $config The logger configuration
     */
    protected function configureErrorLogHandler(
        Logger $logger,
        array $config
    ) : void {
        $config = array_merge(
            [
                'messageType'       => ErrorLogHandler::OPERATING_SYSTEM,
                'level'             => Logger::DEBUG,
                'bubble'            => true,
                'expandNewLines'    => false
            ],
            $config
        );

        $logger->pushHandler(new ErrorLogHandler(
            $config['messageType'],
            $config['level'],
            $config['bubble'],
            $config['expandNewLines']
        ));
    }

    /**
     * Configures and adds a Streamhandler.
     *
     * @param Logger $logger The Logger to add the StreamHandler to.
     * @param array $config The StreamHandler configuration.
     * @throws InvalidConfigurationException if the configuration is invalid.
     */
    protected function configureStreamHandler(
        Logger $logger,
        array $config
    ) : void {
        $config = array_merge(
            [
                'resource' => null,
                'level'    => Logger::DEBUG,
                'bubble'   => true,
                'filePermission' => null,
                'useLocking' => false,
            ],
            $config
        );

        // The one config we need.
        if (empty($config['resource'])) {
            throw new InvalidConfigurationException(
                'StreamHandler "resource" is required.'
            );
        }

        try {
            $logger->pushHandler(new StreamHandler(
                $config['resource'],
                $config['level'],
                $config['bubble'],
                $config['filePermission'],
                $config['useLocking']
            ));
        } catch (Exception $handlerException) {
            throw new InvalidConfigurationException(
                'An error occurred while creating a StreamHandler. ' .
                'See the inner exception for details.',
                0,
                $handlerException
            );
        }
    }

    /**
     * Creates and adds an ErrorLogHandler to the Logger.
     *
     * @param Logger $logger The logger
     * @param array $config The logger configuration
     * @throws InvalidConfigurationException if config is invalid.
     */
    protected function configureSendGridHandler(
        Logger $logger,
        array $config
    ) : void {
        $config = array_merge(
            [
                'apiUser' => null,
                'apiKey' => null,
                'from' => null,
                'to' => null,
                'subject' => null,
                'level' => Logger::DEBUG,
                'bubble' => true
            ],
            $config
        );

        $required = ['apiUser', 'apiKey', 'from', 'to', 'subject'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new InvalidConfigurationException("$field is required.");
            }
        }

        $logger->pushHandler(new SendGridHandler(
            $config['apiUser'],
            $config['apiKey'],
            $config['from'],
            $config['to'],
            $config['subject'],
            $config['level'],
            $config['bubble']
        ));
    }

    /**
     * @param Logger $logger
     * @param array $handlerConfig
     * @throws InvalidConfigurationException
     */
    protected function configureSwiftMailHandler(Logger $logger, array $handlerConfig)
    {
        $config = array_merge(
            [
                'mailer' => null,
                'message' => null,
                'level'   => Logger::ERROR,
                'bubble'  => true,
            ],
            $handlerConfig
        );

        $required = ['mailer', 'message'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new InvalidConfigurationException("$field is required.");
            }
        }

        $logger->pushHandler(new SwiftMailerHandler(
            $config['mailer'],
            $config['message'],
            $config['level'],
            $config['bubble']
        ));
    }
}
