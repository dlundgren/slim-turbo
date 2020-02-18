<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\RouteCollector
 */

namespace Slim\Turbo\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use RuntimeException;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteParser;

class RouteCollector
	extends \Slim\Routing\RouteCollector
{
	/**
	 * @var array List of cached routes
	 */
	protected $cache = [];

	public function __construct(
		ResponseFactoryInterface $responseFactory,
		?ContainerInterface $container = null,
		array $routes = []
	) {
		$this->responseFactory = $responseFactory;
		$this->container       = $container;
		$this->routeParser     = $routeParser ?? new RouteParser($this);
		$this->routes          = $routes;
	}

	/**
	 * Overrides the parent to lookup the route and build it if required
	 *
	 * {@inheritDoc}
	 */
	public function getNamedRoute(string $name): RouteInterface
	{
		if (isset($this->routes[$name])) {
			if (!isset($this->cache[$name])) {
				// NB: during development we may not have a cache built
				if ($this->routes[$name] instanceof Route) {
					return $this->routes[$name];
				}
				$this->cache[$name] = $this->buildRoute($name, ...$this->routes[$name]);
			}

			return $this->cache[$name];
		}

		throw new RuntimeException('Named route does not exist for name: ' . $name);
	}

	/**
	 * Overrides the parent to change where the lookup for the route occurs
	 *
	 * {@inheritDoc}
	 */
	public function lookupRoute(string $identifier): RouteInterface
	{
		if (!isset($this->routes[$identifier])) {
			throw new RuntimeException("Route not found {$identifier}, looks like your route cache is stale.");
		}

		return $this->getNamedRoute($identifier);
	}

	/**
	 * Overrides the parent createRoute so that we don't need a strategy, as route callables are MiddlewareInterface's
	 *
	 * {@inheritDoc}
	 */
	protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
	{
		return new Route(
			$methods,
			$pattern,
			$callable,
			$this->responseFactory,
			$this->container,
			$this->routeGroups,
			$this->routeCounter
		);
	}

	/**
	 * Builds our custom Route object
	 *
	 * @param string          $name
	 * @param string          $pattern
	 * @param string|callable $callable
	 * @param string|array    $middlewares
	 * @return Route
	 */
	protected function buildRoute(string $name, string $pattern, $callable, $middlewares): Route
	{
		/** @var Route $route */
		$route = $this->createRoute([], $pattern, $callable);
		$route->setName($name);
		if (is_array($middlewares)) {
			foreach ($middlewares as $middleware) {
				$route->add($middleware);
			}
		}

		return $route;
	}
}