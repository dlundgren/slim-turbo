<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Router
 */

namespace Slim\Turbo\Routing;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollectorProxy;

/**
 * Replaces Slim\App when passed to a route provider
 *
 * @package Slim\Turbo\Routing
 */
class Router
	extends RouteCollectorProxy
{
	public function __construct(RouteCollectorInterface $routeCollector, $groupPattern = '')
	{
		$this->routeCollector = $routeCollector;
		$this->groupPattern   = $groupPattern;
	}
}