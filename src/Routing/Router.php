<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Router
 */

namespace Slim\Turbo\Routing;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollectorProxy;

/**
 * Replaces Slim\App when passed to a route provider
 *
 * @package Slim\Turbo\Routing
 */
class Router
	extends RouteCollectorProxy
{
	/**
	 * @var bool Whether or not domain routing is supported by the collector
	 */
	protected $supportsDomainRouting = false;

	public function __construct(RouteCollectorInterface $routeCollector, $groupPattern = '')
	{
		$this->routeCollector        = $routeCollector;
		$this->groupPattern          = $groupPattern;
		$this->supportsDomainRouting = $routeCollector instanceof RouteCollector;
	}

	/**
	 * Creates a domain level route
	 *
	 * @NOTE domain routing can only be done at the root level
	 *
	 * @param string          $pattern
	 * @param string|callable $callable
	 *
	 * @return RouteGroupInterface
	 */
	public function domain(string $pattern, $callable): RouteGroupInterface
	{
		if ($this->routeCollector instanceof RouteCollector) {
			return $this->routeCollector->domain($pattern, $callable);
		}

		throw new \RuntimeException("Domain routing is not currently supported. " .
									"Please use " . RouteCollector::class . ' or a derivative.');
	}
}