<?php

/**
 * @file
 * Contains Slim\Turbo\NullResolver
 */

namespace Slim\Turbo;

use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\CallableResolverInterface;

/**
 * Implements CallableResolverInterface to itself.
 *
 * @package Slim\Turbo
 */
class NullResolver
	implements CallableResolverInterface
{
	public function resolve($toResolve): callable
	{
		return $this;
	}

	/**
	 * While detected as callable, this class shouldn't be used.
	 *
	 * @throws \RuntimeException
	 */
	public function __invoke()
	{
		throw new \RuntimeException("Please use " . MiddlewareInterface::class . " instances");
	}
}