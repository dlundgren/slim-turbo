<?php

/**
 * @file
 * Contains Slim\Turbo\Middleware\AcceptsParameters
 */

namespace Slim\Turbo\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * This treats the middleware as immutable to help protect the original instance.
 *
 * @package Slim\Turbo\Middleware
 */
trait AcceptsParameters
{
	/**
	 * @var mixed
	 */
	protected $parameters;

	/**
	 * The parameters that are to be stored in a cloned instance of the middleware
	 *
	 * @param mixed $parameters Parameters to store, by default this is an array
	 *
	 * @return MiddlewareInterface
	 */
	public function withParameters($parameters): MiddlewareInterface
	{
		$clone             = clone $this;
		$clone->parameters = $parameters;

		return $clone;
	}

	/**
	 * @return mixed The parameters
	 */
	public function parameters()
	{
		return $this->parameters;
	}
}