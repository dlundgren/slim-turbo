<?php

namespace Slim\Turbo\Routing;

use DI\Container;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\RoutingMiddleware;

class RouteRunnerTest
	extends TestCase
{
	public function testHandleIgnoresRoutingMiddleware()
	{
		$route   = new class
			implements RequestHandlerInterface
		{
			public function handle(ServerRequestInterface $request): ResponseInterface
			{
				return Factory::createResponse(222);
			}
		};
		$request = Factory::createServerRequest('GET', '/')
						  ->withAttribute('routingResults', true)
						  ->withAttribute('route', $route);

		$response = (new RouteRunner(new Container()))->handle($request);

		self::assertEquals(222, $response->getStatusCode());
	}

	public function testHandleUsesRoutingMiddleware()
	{
		$request   = Factory::createServerRequest('GET', '/')
							->withAttribute(
								'route',
								new class
									implements RequestHandlerInterface
								{
									public function handle(ServerRequestInterface $request): ResponseInterface
									{
										return Factory::createResponse(333);
									}
								}
							);
		$container = new Container();
		$mw        = $this->getMockBuilder(RoutingMiddleware::class)
						  ->disableOriginalConstructor()
						  ->getMock();
		$mw->expects($this->once())->method('performRouting')->willReturn($request);
		$container->set(RoutingMiddleware::class, $mw);

		$response = (new RouteRunner($container))->handle($request);

		self::assertEquals(333, $response->getStatusCode());
	}
}