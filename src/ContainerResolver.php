<?php

/**
 * @file
 * Contains Slim\Turbo\ContainerResolver
 */

namespace Slim\Turbo;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\CallableResolver;
use Slim\Interfaces\CallableResolverInterface;

/**
 * Implements Slims CallableResolver Interface.
 *
 * This checks if the exact callable is in the container first, then falls back to
 * the Slim CallableResolver implementation.
 *
 * @package Slim\Turbo
 */
class ContainerResolver
	implements CallableResolverInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var CallableResolver
	 */
	protected $resolver;

	/**
	 * @var mixed The resolved item
	 */
	protected $resolved;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->resolver  = new CallableResolver($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve($toResolve): callable
	{
		if (is_string($toResolve) && $this->container->has($toResolve)) {
			$this->resolved = $this->container->get($toResolve);
		}
		else {
			$this->resolved = $this->resolver->resolve($toResolve);
		}

		return $this;
	}

	public function __invoke()
	{
		throw new \RuntimeException("Please use " . MiddlewareInterface::class . " instances");
	}
}