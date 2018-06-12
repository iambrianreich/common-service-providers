<?php
/**
 * This file contains the RWC\ServiceProviders\MissingServiceDependencyException class.
 *
 * @author Brian Reich <breich@reich-consulting.net>
 * @copyright 2018 Reich Web Consulting
 * @license MIT https://opensource.org/licenses/MIT
 */
namespace RWC\ServiceProviders;

/**
 * An Exception raised when a dependent service is missing from the Container.
 *
 * @package RWC\ServiceProviders
 */
class MissingServiceDependencyException extends ServiceProviderException
{
}
