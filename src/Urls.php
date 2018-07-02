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
 * A NamedStringProvider hardcoded to the "urls" config. Used to store a list
 * of URLs to be referenced by the application.
 *
 * @package Catalyst\Messaging\Resources
 */
class Urls extends NamedStringProvider implements ServiceProviderInterface
{
    /**
     * Paths constructor.
     */
    public function __construct()
    {
        parent::__construct('urls');
    }
}
