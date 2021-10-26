<?php

namespace Slim\Turbo\Routing;

use DI\Container;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Turbo\Test\RoutedMiddleware;
use Slim\Turbo\Test\TestController;

class RouteTest
	extends TestCase
{
	public function setUp(): void
	{
		$this->di = new Container();
		$this->di->set('test', new TestController());
		$this->di->set('routed', new RoutedMiddleware());
		$this->route = new Route(['GET'], 't', 'test', Factory::getResponseFactory(), $this->di);
	}

	public function testGroupMiddlewareIsAppended()
	{
		$rc    = $this->getMockBuilder(RouteCollectorProxyInterface::class)->getMock();
		$group = new RouteGroup('/', 'test', $rc);
		$group->add('grouped');

		$route = new Route(['GET'], 't', 'test', Factory::getResponseFactory(), $this->di, [$group]);
		$route->add('routed');

		self::assertEquals(['routed', 'grouped'], $route->getMiddleware());
	}

	public function testHandle()
	{
		self::assertEquals(444, $this->route->handle(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testRunAliasesHandle()
	{
		self::assertEquals(444, $this->route->run(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testHandleConvertsCallable()
	{
		$call  = function () {
			return Factory::createResponse(333);
		};
		$route = new Route(['GET'], 't', $call, Factory::getResponseFactory(), $this->di);

		self::assertEquals(333, $route->handle(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testHandlesParameterizedMiddleware()
	{
		$route = new Route(['GET'], 't', 'test', Factory::getResponseFactory(), $this->di);
		$route->add('routed', 'kakaw', 'manager');
		$r = $route->handle(Factory::createServerRequest('GET', '/'));

		self::assertEquals(['kakaw', 'manager'], $r->getHeader('test'));
	}

	public function testHandlesParameterizedMiddlewareByClassName()
	{
		$route = new Route(['GET'], 't', 'test', Factory::getResponseFactory(), $this->di);
		$route->add($middleware = new RoutedMiddleware, 'manager');
		$r = $route->handle(Factory::createServerRequest('GET', '/'));

		self::assertEquals(['manager'], $r->getHeader('test'));
	}
}