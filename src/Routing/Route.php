<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Route
 */

namespace Slim\Turbo\Routing;

use Middlewares\Utils\CallableHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Interfaces\RouteInterface;
use Slim\Turbo\MiddlewareDispatcher;

/**
 * Due to our caching nature, we do not use the CallableResolver in Routes.
 *
 * We also use our custom MiddlewareDispatcher
 *
 * @package Slim\Turbo\Routing
 */
class Route
	extends \Slim\Routing\Route
{
	/**
	 * @var RouteCollector
	 */
	protected $routeCollector;

	public function __construct(
		array $methods,
		string $pattern,
		$callable,
		ResponseFactoryInterface $responseFactory,
		?ContainerInterface $container = null,
		array $groups = [],
		int $identifier = 0
	) {
		$this->methods              = $methods;
		$this->pattern              = $pattern;
		$this->callable             = $callable;
		$this->responseFactory      = $responseFactory;
		$this->container            = $container;
		$this->groups               = $groups;
		$this->identifier           = 'route' . $identifier;
		$this->middlewareDispatcher = new MiddlewareDispatcher($container);
	}

	public function watchNameChange(RouteCollector $routeCollector)
	{
		$this->routeCollector = $routeCollector;
	}

	/**
	 * Overrides the parent so that we can notify the route collector of name changes
	 *
	 * {@inheritDoc}
	 */
	public function setName(string $name): RouteInterface
	{
		if (isset($this->routeCollector)) {
			$this->routeCollector->updateRouteName($this->name ?? $this->identifier, $name);
		}

		parent::setName($name);

		return $this;
	}

	/**
	 * @return array Returns the list of middleware that this route is using
	 */
	public function getMiddleware()
	{
		if (!$this->groupMiddlewareAppended) {
			// due to the way that we handle this we can't use Slims version
			foreach (array_reverse($this->groups) as $group) {
				$group->appendMiddlewareToDispatcher($this->middlewareDispatcher);
			}
			$this->groupMiddlewareAppended = true;
		}

		return $this->middlewareDispatcher->getMiddleware();
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 * @throws HttpInternalServerErrorException
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->middlewareDispatcher->seedMiddlewareStack(
			is_callable($this->callable)
				? new CallableHandler($this->callable)
				: $this->container->get($this->callable)
		);

		return $this->middlewareDispatcher->handle($request);
	}

	/**
	 * Overrides the parent to be an alias to handle
	 *
	 * {@inheritDoc}
	 */
	public function run(ServerRequestInterface $request): ResponseInterface
	{
		return $this->handle($request);
	}
}