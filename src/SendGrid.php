<?php
/**
 * This file contains the RWC\ServiceProviders\SendGrid class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright 2018 Reich Web Consulting
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace RWC\ServiceProviders;

use Pimple\Container;
use Pimple\Exception\UnknownIdentifierException;
use Pimple\ServiceProviderInterface;
use SendGrid as SendGridClient;

/**
 * Provides a SendGrid client object configured with an API Key.
 *
 * The SendGrid ServiceProvider is dependent on a "config" service in the
 * Container. The config must be an array or array-accessible object that
 * contains a "sendgrid" configuration.
 *
 * The "sendgrid" configuration can be
 * specified one of two ways: it can be specified as a string which provides
 * the API key. Or, it can be specified as an array which contains an "apiKey"
 * index specifying the SendGrid API key, as well as an optional "options"
 * index which provides an array of options to pass to the SendGrid client.
 *
 * The SendGrid ServiceProvider registers multiple providers with the Container.
 * To receive the same SendGrid instance every time it is requested, request the
 * "sendgrid" or "SendGrid" index from the Container. To get a new instance
 * of the SendGrid every time, access "sendgridFactory" or SendGridFactory"
 * of the SendGrid every time, access "sendgridFactory" or SendGridFactory"
 *
 * @package RWC\ServiceProviders
 */
class SendGrid implements ServiceProviderInterface
{
    /**
     * Registers the resource on the container.
     *
     * @param Container $pimple The container.
     */
    public function register(Container $pimple)
    {
        // Register the resource.
        $callback = function (Container $resources) {
            return $this->getSendGrid($resources);
        };

        $pimple[SendGrid::class] =  $callback;
        $pimple['sendgrid'] = $callback;
        $pimple['sendgridFactory'] = $pimple->factory($callback);
        $pimple[SendGrid::class . 'Factory'] = $pimple->factory($callback);
    }

    /**
     * Returns a SendGrid instance.
     *
     * The Resources configuration must specify a
     * @param Container $resources
     * @return SendGridClient
     * @throws ServiceProviderException
     */
    private function getSendGrid(Container $resources)
    {
        $config = $this->getConfiguration($resources);

        return new SendGridClient($config['apiKey'], $config['options']);
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
       try {
            // Make sure we have a config resource.
            $config = $resources->offsetGet('config');
        } catch (UnknownIdentifierException $configNotFound) {
            throw new MissingServiceDependencyException(
                "Container did not provide dependent \"config\" service."
            );
        }
        
        // Make sure we have a sendgrid configuration.
        $config = $config['sendgrid'] ?? null;
        if (is_null($config)) {
            throw new InvalidConfigurationException(
                'Required configuration "sendgrid" not found.'
            );
        }

        /*
         * The config can just be a string. That's not a problem. We'll just
         * use that as the API key.
         */
        if (is_string($config)) {
            $config = [ 'apiKey' => $config, 'options' => [] ];
        }

        // By this point, the configuration should be an array.
        if (! is_array($config)) {
            throw new InvalidConfigurationException(
                'Sendgrid configuration should be a string or array.'
            );
        }

        // Merge in sensible defaults.
        $config = array_merge(['apiKey' => null, 'options' => null], $config);

        /*
         * Now we should have an array.  Make sure it contains all requirements.
         */
        if (empty($config['apiKey'])) {
            throw new InvalidConfigurationException(
                'Required configuration "apiKey" not set.'
            );
        }

        return $config;
    }
}