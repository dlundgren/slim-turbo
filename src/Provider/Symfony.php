<?php

/**
 * @file
 * Contains Slim\Turbo\Provider\Symfony
 */

namespace Slim\Turbo\Provider;

use FastRoute\Dispatcher as FastRouteDispatcher;
use Middlewares\Utils\Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Middleware\OutputBufferingMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Slim\Routing\FastRouteDispatcher as SlimFastRouteDispatcher;
use Slim\Routing\RouteParser;
use Slim\Routing\RouteResolver;
use Slim\Turbo\NullResolver;
use Slim\Turbo\MiddlewareDispatcher;
use Slim\Turbo\Routing\Cache\Factory as RouteCacheFactory;
use Slim\Turbo\Routing\Cache\Memory;
use Slim\Turbo\Routing\Dispatcher;
use Slim\Turbo\Routing\RouteCollector;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Turbo\Routing\RouteRunner;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Symfony Dependency Injection service provider
 *
 * This configures the various services used by Slim Turbo
 *
 * @package Slim\Turbo\Provider
 */
class Symfony
	implements ExtensionInterface
{
	const ALIAS        = 'slim_turbo';
	const CACHE_KEY    = 'route_cache';
	const PROVIDER_KEY = 'route_provider';

	public function load(array $configs, ContainerBuilder $container)
	{
		self::register($container);

		// if we have a routing.provider add it
		if (isset($configs[0][self::PROVIDER_KEY])) {
			$provider = $configs[0][self::PROVIDER_KEY];
			if (class_exists($provider)) {
				$container->register(RouteProvider::class, $provider);
			}
		}

		// ensure that the routing.cache key is set...
		$noCache = true;
		if (isset($configs[0][self::CACHE_KEY])) {
			$cache = $configs[0][self::CACHE_KEY];
			if (class_exists($cache)) {
				$noCache = false;
				$container->setAlias(RouteProvider::CACHE_KEY, $cache);
			}
		}

		if ($noCache) {
			$container->register(RouteProvider::CACHE_KEY, Memory::class);
		}
	}

	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getXsdValidationBasePath()
	{
		return false;
	}

	public function getAlias()
	{
		return self::ALIAS;
	}

	public static function register(ContainerBuilder $builder)
	{
		$builder->setParameter('app.middleware', []);
		$containerReference = new Reference(ContainerInterface::class);

		$builder->register(RouteProvider::CACHE_KEY)->setSynthetic(true);
		$builder->register(RouteProvider::NAMED_ROUTE_KEY)
				->setClass(\stdClass::class)
				->setFactory([RouteCacheFactory::class, 'build'])
				->setArguments(
					[
						'$key'      => RouteCacheFactory::NAMED_ROUTES,
						'$cache'    => new Reference(RouteProvider::CACHE_KEY),
						'$provider' => new Reference(RouteProvider::class)
					]
				);
		$builder->register(RouteProvider::DISPATCH_DATA_KEY)
				->setClass(\stdClass::class)
				->setFactory([RouteCacheFactory::class, 'build'])
				->setArguments(
					[
						'$key'      => RouteCacheFactory::DISPATCH_DATA,
						'$cache'    => new Reference(RouteProvider::CACHE_KEY),
						'$provider' => new Reference(RouteProvider::class)
					]
				);

		if (!$builder->hasDefinition(ResponseFactoryInterface::class)) {
			$builder->register(ResponseFactoryInterface::class)
					->setFactory([Factory::class, 'getResponseFactory']);
		}
		if (!$builder->hasDefinition(StreamFactoryInterface::class)) {
			$builder->register(StreamFactoryInterface::class)
					->setFactory([Factory::class, 'getStreamFactory']);
		}

		// Slim replacements
		$builder->register(CallableResolverInterface::class, NullResolver::class);
		$builder->register(RouteCollectorInterface::class, RouteCollector::class)
				->setArguments(
					[
						'$responseFactory' => new Reference(ResponseFactoryInterface::class),
						'$container'       => new Reference($containerReference),
						'$routes'          => new Reference(RouteProvider::NAMED_ROUTE_KEY)
					]
				);
		$builder->register(MiddlewareDispatcher::class)
				->setArguments(
					[
						'$container'  => $containerReference,
						'$middleware' => '%app.middleware%'
					]
				);

		$builder->register(FastRouteDispatcher::class, SlimFastRouteDispatcher::class)
				->setArgument('$data', new Reference(RouteProvider::DISPATCH_DATA_KEY));
		$builder->setAlias(MiddlewareDispatcherInterface::class, MiddlewareDispatcher::class);

		// slim configuration
		$builder->register(DispatcherInterface::class, Dispatcher::class)
				->setArguments(
					[
						'$routeCollector' => new Reference(RouteCollectorInterface::class),
						'$dispatcher'     => new Reference(FastRouteDispatcher::class)
					]
				);
		$builder->register(RouteRunner::class)
				->setArgument('$container', $containerReference);
		$builder->register(RouteResolverInterface::class, RouteResolver::class)
				->setArguments(
					[
						'$routeCollector' => new Reference(RouteCollectorInterface::class),
						'$dispatcher'     => new Reference(DispatcherInterface::class)
					]
				);

		$builder->register(RouteParserInterface::class, RouteParser::class)
				->setArgument('$routeCollector', new Reference(RouteCollectorInterface::class))
				->setPublic(true);
		$builder->register(App::class)
				->setArguments(
					[
						'$responseFactory'      => new Reference(ResponseFactoryInterface::class),
						'$container'            => new Reference(ContainerInterface::class),
						'$callableResolver'     => new Reference(CallableResolverInterface::class),
						'$routeCollector'       => new Reference(RouteCollectorInterface::class),
						'$routeResolver'        => new Reference(RouteResolverInterface::class),
						'$middlewareDispatcher' => new Reference(MiddlewareDispatcherInterface::class)
					]
				)
				->setPublic(true);

		// register all of Slim's middleware as public
		$builder->register(BodyParsingMiddleware::class)->setPublic(true);
		$builder->register(ContentLengthMiddleware::class)->setPublic(true);
		$builder->register(ErrorMiddleware::class)
				->setArguments(
					[
						'$callableResolver' => new Reference(CallableResolverInterface::class),
						'$responseFactory'  => new Reference(ResponseFactoryInterface::class),
					]
				)
				->setPublic(true);
		$builder->register(MethodOverrideMiddleware::class)->setPublic(true);
		$builder->register(OutputBufferingMiddleware::class)
				->setArguments(
					[
						'$streamFactory' => new Reference(StreamFactoryInterface::class),
					]
				)
				->setPublic(true);
		$builder->register(RoutingMiddleware::class)
				->setArguments(
					[
						'$routeResolver' => new Reference(RouteResolverInterface::class),
						'$routeParser'   => new Reference(RouteParserInterface::class)
					]
				)
				->setPublic(true);
	}
}