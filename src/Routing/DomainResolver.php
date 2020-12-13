<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\DomainResolver
 */

namespace Slim\Turbo\Routing;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\RoutingResults;

class DomainResolver
	implements RouteResolverInterface
{
	const DEFAULT_IGNORED_DOMAINS = [
		'localhost'
	];

	/**
	 * @var bool Strips the domain to only route on subdomains
	 */
	protected $useSubdomainOnly = true;

	/**
	 * @var array List of hosts which should not be included in routing
	 */
	protected $ignoreHosts = [];

	/**
	 * @var RouteCollectorInterface
	 */
	protected $routeCollector;

	/**
	 * @var DispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * @param RouteCollectorInterface  $routeCollector
	 * @param DispatcherInterface|null $dispatcher
	 * @param bool                     $useSubdomainOnly
	 * @param array                    $ignoreHosts
	 */
	public function __construct(
		RouteCollectorInterface $routeCollector,
		?DispatcherInterface $dispatcher = null,
		bool $useSubdomainOnly = true,
		array $ignoreHosts = self::DEFAULT_IGNORED_DOMAINS
	) {
		$this->routeCollector   = $routeCollector;
		$this->dispatcher       = $dispatcher ?? new \Slim\Routing\Dispatcher($routeCollector);
		$this->useSubdomainOnly = $useSubdomainOnly;
		$this->ignoreHosts      = $ignoreHosts;
	}

	/**
	 * Resolves the route given a URI
	 *
	 * This will use domains as well
	 *
	 * @param UriInterface $uri
	 * @param string       $method
	 *
	 * @return RoutingResults
	 */
	public function resolveRouteFromUri(UriInterface $uri, string $method): RoutingResults
	{
		return $this->handleDispatch(
			$method,
			$uri->getPath(),
			$this->formatHost($uri->getHost())
		);
	}

	/**
	 * @param string $uri Should be $request->getUri()->getPath()
	 * @param string $method
	 *
	 * @return RoutingResults
	 */
	public function computeRoutingResults(string $uri, string $method): RoutingResults
	{
		return $this->handleDispatch($method, $uri);
	}

	/**
	 * Common function for dealing with the both the host and path
	 *
	 * @param string $method
	 * @param string $path
	 * @param string $host
	 *
	 * @return RoutingResults
	 */
	protected function handleDispatch(string $method, string $path, string $host = ''): RoutingResults
	{
		$path = rawurldecode($path);
		if ($path === '' || $path[0] !== '/') {
			$path = '/' . $path;
		}

		return $this->dispatcher->dispatch($method, $host . $path);
	}

	/**
	 * @param string $identifier
	 *
	 * @return RouteInterface
	 */
	public function resolveRoute(string $identifier): RouteInterface
	{
		return $this->routeCollector->lookupRoute($identifier);
	}

	/**
	 * Resolves the given host to it's routing set
	 */
	protected function formatHost(string $host): string
	{
		if (in_array($host, $this->ignoreHosts)) {
			return '';
		}

		if ($this->useSubdomainOnly) {
			$count = substr_count($host, '.');
			if ($count === 1 || $count === 2 && strpos($host, 'www.') !== false) {
				$host = '';
			}
			elseif ($count >= 2) {
				$host = substr($host, 0, (int)strrpos($host, '.', 0 - (int)strrpos($host, '.')));
			}
		}

		return empty($host) ? '' : "{$host}:";
	}
}