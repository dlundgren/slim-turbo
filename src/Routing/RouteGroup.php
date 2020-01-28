<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\RouteGroup
 */

namespace Slim\Turbo\Routing;

use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;

/**
 * Due to our caching nature, we do not use the CallableResolver in RouteGroups
 *
 * @package Slim\Turbo\Routing
 */
class RouteGroup
	extends \Slim\Routing\RouteGroup
{
	public function __construct(string $pattern, $callable, RouteCollectorProxyInterface $routeCollectorProxy)
	{
		$this->pattern             = $pattern;
		$this->callable            = $callable;
		$this->routeCollectorProxy = $routeCollectorProxy;
	}

	/**
	 * {@inheritdoc}
	 */
	public function collectRoutes(): RouteGroupInterface
	{
		if (is_callable($this->callable)) {
			($this->callable)($this->routeCollectorProxy);
		}

		return $this;
	}
}