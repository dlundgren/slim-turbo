<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\DomainRouter
 */

namespace Slim\Turbo\Routing;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy;

/**
 * Changes the groupPattern to be eligible for our domain routing
 *
 * @package Slim\Turbo\Routing
 */
class DomainRouter
	extends Router
{
	public function __construct(RouteCollectorInterface $routeCollector, string $groupPattern)
	{
		parent::__construct($routeCollector, trim($groupPattern, '/:'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function map(array $methods, string $pattern, $callable): RouteInterface
	{
		return $this->routeCollector->map(
			$methods,
			($this->groupPattern ? "{$this->groupPattern}:" : "") . $pattern,
			$callable
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function group(string $pattern, $callable): RouteGroupInterface
	{
		return $this->routeCollector->group(
			($this->groupPattern ? "{$this->groupPattern}:" : "") . $pattern,
			$callable
		);
	}
}