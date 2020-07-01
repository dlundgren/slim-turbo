<?php

namespace Slim\Turbo\Middleware;

use Middlewares\Utils\Factory;
use PHPStan\Testing\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;
use Slim\Turbo\Routing\Dispatcher;
use Slim\Turbo\Routing\DomainResolver;
use Slim\Turbo\Routing\Route;

class DomainRoutingTest
	extends TestCase
{
	public function provideExceptionPayloads()
	{
		return [
			[\RuntimeException::class, -1],
			[HttpNotFoundException::class, RoutingResults::NOT_FOUND],
		];
	}

	/**
	 * @dataProvider provideExceptionPayloads
	 */
	public function testPerformRoutingThrowsException($exceptionClass, $routingStatus)
	{
		$dr = $this->getMockBuilder(DomainResolver::class)->disableOriginalConstructor()->getMock();
		$rp = $this->getMockBuilder(RouteParserInterface::class)->getMock();
		$dp = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->getMock();
		$dr->method('resolveRouteFromUri')->willReturn(new RoutingResults($dp, 'GET', '/test', $routingStatus));

		$mw = new DomainRouting($dr, $rp);

		$this->expectException($exceptionClass);
		$mw->performRouting(Factory::createServerRequest('GET', '/test'));
	}

	public function testPerformRoutingAddMethodsOnNotAllowedMethodException()
	{
		$dr = $this->getMockBuilder(DomainResolver::class)->disableOriginalConstructor()->getMock();
		$rp = $this->getMockBuilder(RouteParserInterface::class)->getMock();
		$dp = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->getMock();
		$dr->method('resolveRouteFromUri')->willReturn(
			new RoutingResults($dp, 'GET', '/test', RoutingResults::METHOD_NOT_ALLOWED)
		);

		$mw = new DomainRouting($dr, $rp);

		$this->expectException(HttpMethodNotAllowedException::class);
		$mw->performRouting(Factory::createServerRequest('GET', '/test'));
		/** @var HttpMethodNotAllowedException $exception */
		$exception = $this->getExpectedException();
		self::assertEquals(['GET'], $exception->getAllowedMethods());
	}

	public function testPerformRoutingReturnsRequestWithRouteAttribute()
	{
		$dr = $this->getMockBuilder(DomainResolver::class)->disableOriginalConstructor()->getMock();
		$rp = $this->getMockBuilder(RouteParserInterface::class)->getMock();
		$dp = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->getMock();
		$mw = new DomainRouting($dr, $rp);

		$dr->method('resolveRouteFromUri')->willReturn(
			new RoutingResults($dp, 'GET', '/test', RoutingResults::FOUND)
		);
		$request = $mw->performRouting(Factory::createServerRequest('GET', '/test'));

		self::assertInstanceOf(ServerRequestInterface::class, $request);
		self::assertNotNull($request->getAttribute(RouteContext::ROUTE));
	}
}