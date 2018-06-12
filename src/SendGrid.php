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
use Pimple\ServiceProviderInterface;
use SendGrid as SendGridClient;

/**
 * Provides a SendGrid client object configured with an apiKey.
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
        $config = $resources['config'];

        if (! isset($config['sendgrid'])) {
            throw new ServiceProviderException(
                'Required configuration "sendgrid" not found.'
            );
        }

        // SendGrid configuration, api key, and connection options.
        $config          = $config['sendgrid'];
        $apiKey          = null;
        $sendGridOptions = [];

        // If the config is just a string, assume api key.
        if (is_string($config)) {
            $apiKey = $config;
        }

        // Process array
        if (is_array($config)) {
            // Make sure it has an apiKey
            if (! isset($config['apiKey'])) {
                throw new ServiceProviderException(
                    'Required configuration "sendgrid\apiKey" not found.'
                );
            }

            $apiKey = $config['apiKey'];

            // If it has an options array, use it.
            if (isset($config['options'])) {
                $sendGridOptions = $config['options'];
            }
        }

        return new SendGridClient($apiKey, $sendGridOptions);
    }
}