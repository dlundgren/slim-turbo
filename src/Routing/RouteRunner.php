<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\RouteRunner
 */

namespace Slim\Turbo\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\RoutingMiddleware;
use Slim\Routing\RouteContext;

class RouteRunner
	implements RequestHandlerInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		// If routing hasn't been done, then do it now so we can dispatch
		if ($request->getAttribute(RouteContext::ROUTING_RESULTS) === null) {
			$request = $this->container->get(RoutingMiddleware::class)->performRouting($request);
		}

		return $request->getAttribute(RouteContext::ROUTE)->handle($request);
	}
}