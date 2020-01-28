<?php

namespace Slim\Turbo\Routing;

use DI\Container;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Turbo\Test\TestController;

class RouteTest
	extends TestCase
{
	public function setUp()
	{
		$this->di = new Container();
		$this->di->set('test', new TestController());
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
}