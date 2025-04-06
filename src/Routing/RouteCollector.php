<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\RouteCollector
 */

namespace Slim\Turbo\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteParser;
use Slim\Turbo\Exception\InvalidRoute;

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
		$this->routes          = $routes;
	}

	/**
	 * Overrides the parent to handle our customized RouteGroup and Router
	 *
	 * {@inheritDoc}
	 */
	public function domain(string $pattern, $callable): RouteGroupInterface
	{
		if (count($this->routeGroups)) {
			throw new \RuntimeException("Domain routing cannot be set within groups");
		}

		$router              = new DomainRouter($this, $pattern);
		$routeGroup          = new RouteGroup($pattern, $callable, $router);
		$this->routeGroups[] = $routeGroup;

		$routeGroup->collectRoutes();
		array_pop($this->routeGroups);

		return $routeGroup;
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

		throw new InvalidRoute("Named route does not exist for name: {$name}");
	}

	/**
	 * Returns the RouteParserInterface as defined in the container or creates the RouteParser
	 *
	 * {@inheritDoc}
	 */
	public function getRouteParser(): RouteParserInterface
	{
		if (isset($this->routeParser)) {
			return $this->routeParser;
		}

		if ($this->container && $this->container->has(RouteParserInterface::class)) {
			$this->routeParser = $this->container->get(RouteParserInterface::class);
		}

		return $this->routeParser ?? ($this->routeParser = new RouteParser($this));
	}

	/**
	 * Overrides the parent to handle our customized RouteGroup and Router
	 *
	 * {@inheritDoc}
	 */
	public function group(string $pattern, $callable): RouteGroupInterface
	{
		$router              = new Router($this, $pattern);
		$routeGroup          = new RouteGroup($pattern, $callable, $router);
		$this->routeGroups[] = $routeGroup;

		$routeGroup->collectRoutes();
		array_pop($this->routeGroups);

		return $routeGroup;
	}

	/**
	 * Overrides the parent to change where the lookup for the route occurs
	 *
	 * {@inheritDoc}
	 */
	public function lookupRoute(string $identifier): RouteInterface
	{
		if (!isset($this->routes[$identifier])) {
			throw new InvalidRoute("Route not found {$identifier}, looks like your route cache is stale.");
		}

		return $this->getNamedRoute($identifier);
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
	 * Builds our custom Route object
	 *
	 * @param string          $name
	 * @param string          $pattern
	 * @param string|callable $callable
	 * @param string|array    $middlewares
	 *
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

	/**
	 * Overrides the parent createRoute so that we don't need a strategy, as route callables are MiddlewareInterface's
	 *
	 * {@inheritDoc}
	 */
	protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
	{
		$route = new Route(
			$methods,
			$pattern,
			$callable,
			$this->responseFactory,
			$this->container,
			$this->routeGroups,
			$this->routeCounter
		);
		$route->watchNameChange($this);

		return $route;
	}
}