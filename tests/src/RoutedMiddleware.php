<?php

namespace Slim\Turbo\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Turbo\Middleware\AcceptsParameters;
use Slim\Turbo\Middleware\ParameterAware;

class RoutedMiddleware
	implements MiddlewareInterface, ParameterAware
{
	use AcceptsParameters;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response  = $handler->handle($request);

		return $response->withHeader('test', $this->parameters);
	}
}