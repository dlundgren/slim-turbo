<?php

/**
 * @file
 * Contains Slim\Turbo\Provider\RouteProvider
 */

namespace Slim\Turbo\Provider;

use Slim\Interfaces\RouteCollectorProxyInterface;

/**
 * Contract for a Route Provider that is registered in the DI system
 *
 * @package Slim\Turbo\Provider
 */
interface RouteProvider
{
	const CACHE_KEY         = 'routing.cache';
	const NAMED_ROUTE_KEY   = 'routing.named_routes';
	const DISPATCH_DATA_KEY = 'routing.dispatch_data';

	/**
	 * Registers the routes with the router
	 *
	 * @param RouteCollectorProxyInterface $router
	 * @return mixed
	 */
	public function register(RouteCollectorProxyInterface $router);
}