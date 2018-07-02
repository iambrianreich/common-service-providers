[![Build Status](https://travis-ci.org/reichwebconsulting/common-service-providers.svg?branch=master)](https://travis-ci.org/reichwebconsulting/common-service-providers)

Do you use Pimple? Do you find yourself writing the same boilerplate code
across multiple projects to access the same or similar resources?  *Then this
project is for you.*

**reichwebconsulting/common-service-providers** offers a set of `ServiceProviderInterface`
implementations for creating instances of common shared resources.  Stop
writing the same database instantiation code.  Add some configuration and move
on.!

## Requirements
* [Composer](https://getcomposer.org/) must be installed to pull project dependencies.
* PHP 7.2 or higher.

## Installation

    composer require reichwebconsulting/common-service-providers
    
`reichwebconsulting/common-service-providers` defines a number of
ServiceProviderInterface implementations that are dependent on the presence of
libraries that define those resources. See the `suggest` block in `composer.json`
for a list of recommended dependencies you can use to fully leverage this
project.
 
## Contributors
 * [Brian Reich, Reich Web Consulting](https://github.com/reichwebconsulting)

## License

MIT License

Copyright (c) 2018 Reich Web Consulting

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Releases

### 1.1.3 ConfigurationFile Provider

Added ConfigurationFile provider which will provide access to a configuration
returned as an associative array.

### 1.1.4 Fix Monolog Name

The Monolog Provider's "name" field was hardcoded to the name of the client
project this component originally came from. Whoops!

### 1.1.5 Paths Provider

Added the `Paths` Service Provider, which provides named paths from the
service provider configuration.