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
use Slim\Interfaces\RouteInterface;

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

		$this->routes    = $this->generateRoutes($routeCollector = $this->resolveFastRouteCollector());
		$this->data      = $routeCollector->getData();
		$this->generated = true;

		return [$this->routes, $this->data];
	}

	/**
	 * Changes the routes name in our array
	 *
	 * @param string $oldName The old name to change
	 * @param string $newName The new name to set
	 */
	public function updateRouteName($oldName, $newName)
	{
		if ($oldName !== $newName && isset($this->routes[$oldName])) {
			$this->routes[$newName] = $this->routes[$oldName];
			unset($this->routes[$oldName]);
		}
	}

	/**
	 * Overrides the parent createRoute so that we can watch any name changes on our routes
	 *
	 * {@inheritDoc}
	 */
	protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
	{
		$route =  parent::createRoute($methods, $pattern, $callable);

		$route->watchNameChange($this);

		return $route;
	}

	/**
	 * Generates the routes in FastRoute
	 *
	 * @param FastRouteCollector $routeCollector
	 * @param array              $routes
	 *
	 * @return array List of routes that were generated
	 */
	protected function generateRoutes(\FastRoute\RouteCollector $routeCollector, array $routes = [])
	{
		foreach ($this->getRoutes() as $route) {
			/** @var Route $route */
			$id = $route->getName() ?? $route->getIdentifier();
			if (!($route instanceof Route)) {
				// Interfaces are nice but in this case we must insist we have our own
				throw new \RuntimeException("Route is not an instance of " . Route::class);
			}

			$routeCollector->addRoute($route->getMethods(), $route->getPattern(), $id);

			$routes[$id] = [
				$route->getPattern(),
				$route->getCallable(),
				$route->getMiddleware(),
			];
		}

		return $routes;
	}

	/**
	 * DX to allow the collector to be changed without re-writing all the other methods.
	 *
	 * @return FastRouteCollector The FastRoute\RouteCollector instance
	 */
	protected function resolveFastRouteCollector(): FastRouteCollector
	{
		return new FastRouteCollector(new Std(), new GroupCountBased());
	}
}