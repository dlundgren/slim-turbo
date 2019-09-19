<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\CachedCollector
 */

namespace Slim\Turbo\Routing;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Handles the basic setup of the caching data
 *
 * @package Slim\Turbo\Routing
 */
class CachedCollector
	extends RouteCollector
{
	const NAMED_ROUTES_KEY  = 'routing.named_routes';
	const DISPATCH_DATA_KEY = 'routing.dispatch_data';

	/**
	 * @var bool Whether or not the routes have been generated yet
	 */
	protected $generated = false;

	/**
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * @var array Cache of currently built routes
	 */
	protected $data;

	/**
	 * @var array<string, array<int, array|callable|string>>
	 */
	protected $routes;

	public function __construct(ResponseFactoryInterface $responseFactory)
	{
		$this->responseFactory = $responseFactory;
	}

	/**
	 * Builds the routes.
	 */
	public function build()
	{
		if ($this->generated) {
			return [$this->routes, $this->data];
		}

		$routes                  = [];
		$routeDefinitionCallback = function (\FastRoute\RouteCollector $r) use (&$routes) {
			foreach ($this->getRoutes() as $route) {
				/** @var Route $route */
				$id          = $route->getName() ?? $route->getIdentifier();
				if (!($route instanceof Route)) {
					// Interfaces are nice but in this case we must insist we have our own
					throw new \RuntimeException("Route is not an instance of " . Route::class);
				}

				$routes[$id] = [
					$route->getPattern(),
					$route->getCallable(),
					$route->getMiddleware(),
				];

				$r->addRoute($route->getMethods(), $route->getPattern(), $id);
			}
		};

		$routeDefinitionCallback($routeCollector = $this->resolveFastRouteCollector());

		$this->routes    = $routes;
		$this->data      = $routeCollector->getData();
		$this->generated = true;

		return [$this->routes, $this->data];
	}

	protected function resolveFastRouteCollector(): FastRouteCollector
	{
		return new FastRouteCollector(new Std(), new GroupCountBased());
	}
}