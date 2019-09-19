# Slim Turbo

Slim Turbo is an extension to [Slim Framework](https://github.com/slimphp/Slim) that allows you to cache the Route
names and Routes directly in a compiled Dependency Injection container.

It's recommended to use [Slim](https://github.com/slimphp/Slim) without this package, for DI containers that do not compile.

## Installation

It's recommended to use [Composer](https://getcomposer.org) to install Slim Turbo.

```bash
$ composer require dlundgren/slim-turbo:^1.0
```

This will install Slim Turbo and all required dependencies. Like Slim, Slim Turbo requires PHP 7.1.

## Usage

Create a class that extends [Slim\Turbo\Provider\RouteProvider](src/Provider/RouteProvider.php) and implement
the `register()` method. Routes are defined similarly to using `Slim\App` and it's recommended to use class names,
service names, or strings when adding middleware and route callables. These will be loaded from the Container as
needed.
 
**NOTE** Closures may be used with SlimTurbo, but it's recommended to only use them while in development. It is
undefined behavior (from SlimTurbo's perspective) to use Closures in production.

## Caching Routing information

In order to cache the route information that is generated you **MUST** set a `routing.cache` key in your DI
container to a [SimpleCache](https://packagist.org/providers/psr/simple-cache-implementation) implementation. 

## Service Provider Initialization

Slim Turbo provides service providers for the following Dependency Injection containers:

- [Symfony Dependency Injection](examples/symfony/index.php)
- [PHP DI](examples/php-di/index.php)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover security related issues, please email dlundgren@syberisle.net instead of using the issue tracker.

## License

Slim Turbo is licensed under the MIT license. See [License File](LICENSE.md) for more information.