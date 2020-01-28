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
	 * @var array List of groups we are in
	 */
	protected $routeGroups = [];

	public function __construct(RouteCollectorInterface $routeCollector, $groupPattern = '')
	{
		$this->routeCollector = $routeCollector;
		$this->groupPattern   = $groupPattern;
	}

	/**
	 * Overrides the parent in order to handle cached groups
	 *
	 * {@inheritDoc}
	 */
	public function group(string $pattern, $callable): RouteGroupInterface
	{
		$routeCollectorProxy = new self($this->routeCollector, $pattern);
		$routeGroup          = new RouteGroup($pattern, $callable, $routeCollectorProxy);
		$this->routeGroups[] = $routeGroup;

		$routeGroup->collectRoutes();
		array_pop($this->routeGroups);

		return $routeGroup;
	}
}