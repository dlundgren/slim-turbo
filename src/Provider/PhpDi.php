<?php

/**
 * @file
 * Contains Slim\Turbo\Provider\PhpDi
 */

namespace Slim\Turbo\Provider;

use DI\ContainerBuilder;
use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\DefinitionSource;
use function DI\factory;
use function DI\create;
use function DI\get;
use FastRoute\Dispatcher as FastRouteDispatcher;
use Middlewares\Utils\Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\FastRouteDispatcher as SlimFastRouteDispatcher;
use Slim\Routing\RouteParser;
use Slim\Routing\RouteResolver;
use Slim\Turbo\NullResolver;
use Slim\Turbo\MiddlewareDispatcher;
use Slim\Turbo\Routing\Dispatcher;
use Slim\Turbo\Routing\Cache as RoutingCache;
use Slim\Turbo\Routing\RouteCollector;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Turbo\Routing\RouteRunner;

/**
 * PHP DI service provider
 *
 * This configures the various services used by Slim Turbo
 *
 * @package Slim\Turbo\Provider
 */
class PhpDi
{
	public static function definitions()
	{
		return array(
			'app.middleware'                     => [],
			RouteProvider::CACHE_KEY             => null,
			RouteProvider::NAMED_ROUTE_KEY       => factory([RoutingCache\Factory::class, 'build'])
				->parameter('key', RoutingCache\Factory::NAMED_ROUTES)
				->parameter('cache', get(RouteProvider::CACHE_KEY))
				->parameter('provider', get(RouteProvider::class))
			,
			RouteProvider::DISPATCH_DATA_KEY     => factory([RoutingCache\Factory::class, 'build'])
				->parameter('key', RoutingCache\Factory::DISPATCH_DATA)
				->parameter('cache', get(RouteProvider::CACHE_KEY))
				->parameter('provider', get(RouteProvider::class)),
			ResponseFactoryInterface::class      => factory([Factory::class, 'getResponseFactory']),
			CallableResolverInterface::class     => create(NullResolver::class),
			// convert this to a factory
			RouteCollectorInterface::class       => create(RouteCollector::class)
				->constructor(
					get(ResponseFactoryInterface::class),
					get(ContainerInterface::class),
					get(RouteProvider::NAMED_ROUTE_KEY)
				),
			MiddlewareDispatcherInterface::class => create(MiddlewareDispatcher::class)
				->constructor(
					get(ContainerInterface::class),
					get('app.middleware')
				),
			FastRouteDispatcher::class           => create(SlimFastRouteDispatcher::class)
				->constructor(get(RouteProvider::DISPATCH_DATA_KEY)),
			DispatcherInterface::class           => create(Dispatcher::class)
				->constructor(
					get(RouteCollectorInterface::class),
					get(FastRouteDispatcher::class)
				),
			RouteRunner::class                   => create(RouteRunner::class)
				->constructor(
					get(ContainerInterface::class)
				),
			RouteResolverInterface::class        => create(RouteResolver::class)
				->constructor(
					get(RouteCollectorInterface::class),
					get(DispatcherInterface::class)
				),
			RouteParserInterface::class          => create(RouteParser::class)
				->constructor(
					get(RouteCollectorInterface::class)
				)
			,

			// slim app
			App::class                           => create(App::class)
				->constructor(
					get(ResponseFactoryInterface::class),
					get(ContainerInterface::class),
					get(CallableResolverInterface::class),
					get(RouteCollectorInterface::class),
					get(RouteResolverInterface::class),
					get(MiddlewareDispatcherInterface::class)
				)
		);
	}
}
