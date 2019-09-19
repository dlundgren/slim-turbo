<?php

namespace Slim\Turbo\Test;

use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestController
	implements RequestHandlerInterface
{
	public function get()
	{
		throw new \InvalidArgumentException('got here');
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return Factory::createResponse(444);
	}
}