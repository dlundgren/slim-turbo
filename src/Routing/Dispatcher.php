<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Dispatcher
 */

namespace Slim\Turbo\Routing;

use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\FastRouteDispatcher;
use Slim\Routing\RoutingResults;

class Dispatcher
	implements DispatcherInterface
{
	/**
	 * @var RouteCollectorInterface
	 */
	protected $routeCollector;

	/**
	 * @var FastRouteDispatcher
	 */
	protected $dispatcher;

	public function __construct(RouteCollectorInterface $routeCollector, FastRouteDispatcher $dispatcher)
	{
		$this->routeCollector = $routeCollector;
		$this->dispatcher     = $dispatcher;
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(string $method, string $uri): RoutingResults
	{
		$results = $this->dispatcher->dispatch($method, $uri);

		return new RoutingResults($this, $method, $uri, $results[0], $results[1], $results[2]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedMethods(string $uri): array
	{
		return $this->dispatcher->getAllowedMethods($uri);
	}
}