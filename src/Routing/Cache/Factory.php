<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Cache\Factory
 */

namespace Slim\Turbo\Routing\Cache;

use Middlewares\Utils\Factory as MiddlewareFactory;
use Psr\SimpleCache\CacheInterface;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Routing\CachedCollector;
use Slim\Turbo\Routing\Router;

/**
 * Handles building the routing information
 *
 * @package Slim\Turbo\Provider
 */
class Factory
{
	/**
	 * Cache keys
	 */
	const NAMED_ROUTES  = '_named_routes';
	const DISPATCH_DATA = '_dispatch_data';

	/**
	 * @var CacheInterface
	 */
	protected static $cache;

	/**
	 * Builds the requested cache key
	 *
	 * @param string               $key       The key to build
	 * @param RouteProvider        $provider  The route provider for routes
	 * @param CacheInterface|null  $cache     Where the routes should be cached
	 * @param CachedCollector|null $collector Where to store the routes
	 *
	 * @return mixed|null
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public static function build($key,
								 RouteProvider $provider,
								 ?CacheInterface $cache = null,
								 ?CachedCollector $collector = null
	) {
		assert($key === self::NAMED_ROUTES || $key === self::DISPATCH_DATA);

		$cache = $cache ?? (self::$cache ?? (self::$cache = new Memory()));
		if ($data = $cache->get($key, null)) {
			return $data;
		}

		$collector = $collector ?? new CachedCollector(MiddlewareFactory::getResponseFactory());
		$provider->register(new Router($collector));

		list($namedRoutes, $dispatchData) = $collector->build();

		$cache->set(self::NAMED_ROUTES, $namedRoutes);
		$cache->set(self::DISPATCH_DATA, $dispatchData);

		return $cache->get($key, []);
	}
}