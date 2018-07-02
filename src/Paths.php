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
 * The Paths service provide provides named paths as specified in the config
 * service's "paths" parameter. This can be used to specify paths for logging
 * storage, scratch folders, etc.
 *
 * @package Catalyst\Messaging\Resources
 */
class Paths implements ServiceProviderInterface
{
    /**
     * Registers the resource on the container.
     *
     * @param Container $pimple The container.
     */
    public function register(Container $pimple)
    {
        // Register the resource.
        $pimple['paths'] = function () use ($pimple) {
            return function (string $pathName) use ($pimple) : string {
                // Make sure we've got a config
                if (! $pimple->offsetExists('config')) {
                    throw new MissingDependencyException(
                        'Container is missing dependent service "config"'
                    );
                }

                $config = $pimple['config'];

                $paths = $config['paths'] ?? [];
                $path  = $paths[$pathName] ?? null;

                // Make sure path is defined.
                if (is_null($path)) {
                    throw new InvalidConfigurationException(
                        'Paths definition does not contain "' . $pathName .
                        '" definition.'
                    );
                }

                return (string) $path;
            };
        };
    }
}
