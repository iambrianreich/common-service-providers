<?php
/**
 * This file contains the RWC\ServiceProviders\ProviderCreationException class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright 2018 Reich Web Consulting
 * @license MIT https://opensource.org/licenses/MIT
 */
namespace RWC\ServiceProviders;

/**
 * An Exception raised when a service provider is correctly configured but still
 * cannot successfully instantiate the service. For example: a database connection
 * failure.
 *
 * @package RWC\ServiceProviders
 */
class ProviderCreationException extends ServiceProviderException
{
}
