<?php

/**
 * @file
 * Contains Slim\Turbo\MiddlewareDispatcher
 */

namespace Slim\Turbo;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\MiddlewareDispatcher as SlimMiddlewareDispatcher;
use Slim\Routing\RouteRunner as SlimRouteRunner;
use Slim\Turbo\Routing\RouteRunner;

/**
 * Overrides Slim's MiddlewareDispatcher to handle cached middleware lists
 *
 * @package Slim\Turbo
 */
class MiddlewareDispatcher
	extends SlimMiddlewareDispatcher
{
	/**
	 * @var array<string|callable|MiddlewareInterface> List of middleware to load from the container
	 */
	protected $middleware = [];

	public function __construct(
		?ContainerInterface $container = null,
		$middleware = []
	) {
		$this->container = $container;
		if (is_array($middleware)) {
			$this->middleware = $middleware;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function seedMiddlewareStack(RequestHandlerInterface $kernel): void
	{
		if ($kernel instanceof SlimRouteRunner) {
			if ($this->container->has(Routing\RouteRunner::class)) {
				$kernel = $this->container->get(Routing\RouteRunner::class);
			}
			elseif ($this->container) {
				$kernel = new RouteRunner($this->container);
			}
			else {
				throw new \RuntimeException(
					"Unable to change the RouteRunner. Please supply a container or ensure " .
					"that " . RouteRunner::class . " exists in the container."
				);
			}
		}

		parent::seedMiddlewareStack($kernel);
	}

	/**
	 * @return array Returns the list of currently set middleware
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Overrides the parent to allow middleware to be sent in as an array
	 *
	 * NOTE: Unlike the parent this does not resolve the middleware when called
	 *
	 * {@inheritDoc}
	 */
	public function add($middleware): MiddlewareDispatcherInterface
	{
		if (is_array($middleware)) {
			foreach ($middleware as $mw) {
				$this->middleware[] = $mw;
			}
		}
		else {
			$this->middleware[] = $middleware;
		}

		return $this;
	}

	/**
	 * Overrides the parent handle to resolve the middleware before running the route
	 *
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		foreach ($this->middleware as $middleware) {
			if (is_callable($middleware)) {
				$this->addCallable($middleware);
			}
			elseif (is_string($middleware)) {
				$this->addMiddleware($this->container->get($middleware));
			}
			else {
				$this->addMiddleware($middleware);
			}
		}

		return parent::handle($request);
	}
}