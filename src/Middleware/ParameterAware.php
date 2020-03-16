<?php

/**
 * @file
 * Contains Slim\Turbo\Middleware\ParameterAware
 */

namespace Slim\Turbo\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface to indicate that the middleware handles parameters
 *
 * @package Slim\Turbo\Middleware
 */
interface ParameterAware
{
	/**
	 * The parameters that are to be stored in a cloned instance of the middleware
	 *
	 * @param mixed $parameters Parameters to store, by default this is an array
	 *
	 * @return MiddlewareInterface
	 */
	public function withParameters($parameters): MiddlewareInterface;

	/**
	 * @return mixed The parameters
	 */
	public function parameters();
}