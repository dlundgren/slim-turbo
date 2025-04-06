<?php

/**
 * @file
 * Contains Slim\Turbo\Middleware\DomainRouting
 */

namespace Slim\Turbo\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Middleware\RoutingMiddleware;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;
use Slim\Turbo\Routing\DomainResolver;

/**
 * Allows domain based routing to occur
 *
 * This requires the explicit DomainResolver versus the RouteResolverInterface as it uses a different method for
 * resolving the route.
 *
 * @package Slim\Turbo\Middleware
 */
class DomainRouting
	extends RoutingMiddleware
{
	/**
	 * @var DomainResolver
	 */
	protected RouteResolverInterface $routeResolver;

	/**
	 * @param DomainResolver       $routeResolver
	 * @param RouteParserInterface $routeParser
	 */
	public function __construct(DomainResolver $routeResolver, RouteParserInterface $routeParser)
	{
		$this->routeResolver = $routeResolver;
		$this->routeParser   = $routeParser;
	}

	public function performRouting(ServerRequestInterface $request): ServerRequestInterface
	{
		$routingResults = $this->routeResolver->resolveRouteFromUri(
			$request->getUri(),
			$request->getMethod()
		);

		$routeStatus = $routingResults->getRouteStatus();
		$request     = $request->withAttribute(RouteContext::ROUTING_RESULTS, $routingResults);

		switch ($routeStatus) {
			case RoutingResults::FOUND:
				$routeArguments  = $routingResults->getRouteArguments();
				$routeIdentifier = $routingResults->getRouteIdentifier() ?? '';
				$route           = $this->routeResolver
					->resolveRoute($routeIdentifier)
					->prepare($routeArguments);

				return $request->withAttribute(RouteContext::ROUTE, $route);
			case RoutingResults::NOT_FOUND:
				$exception = new HttpNotFoundException($request);
				break;
			case RoutingResults::METHOD_NOT_ALLOWED:
				$exception = new HttpMethodNotAllowedException($request);
				$exception->setAllowedMethods($routingResults->getAllowedMethods());
				break;
			default:
				$exception = new \RuntimeException('An unexpected error occurred while performing routing.');
		}

		throw $exception;
	}
}