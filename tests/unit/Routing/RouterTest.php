<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\CallableResolver;
use Slim\Routing\RouteResolver;
use Slim\Routing\RoutingResults;

class RouterTest
	extends TestCase
{
	/**
	 * @var Router
	 */
	protected $router;

	public function setUp(): void
	{
		$this->router = new Router(
			new CachedCollector(Factory::getResponseFactory()),
			'/'
		);
	}

	public function testGroupUsesCustomObject()
	{
		$this->router->get('k', 'kakaw')->setName('kakaw');
		$group = $this->router->group(
			't',
			function ($router) {
				$router->get('/h', 'test1')->setName('test1');
			}
		);
		self::assertInstanceOf(RouteGroup::class, $group);

		$rc    = $this->router->getRouteCollector();
		$route = $rc->getNamedRoute('test1');

		self::assertEquals('/t/h', $route->getPattern());
	}

	public function testNestedGroups()
	{
		$this->router->group(
			'test',
			function ($router) {
				$router->group(
					'/nested',
					function ($router) {
						$router->get('/group', 'dtest')->setName('dtest');
					}
				);
			}
		);

		$resolver = new RouteResolver($this->router->getRouteCollector());
		$result   = $resolver->computeRoutingResults('/test/nested/group', 'GET');
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
	}

	public function testDomainRouting()
	{
		$this->router->domain(
			'api',
			function ($router) {
				$router->get('/test', 'api_test')->setName('api_test');
			}
		);
		$rc    = $this->router->getRouteCollector();
		$route = $rc->getNamedRoute('api_test');

		self::assertEquals('api:/test', $route->getPattern());
	}

	public function testDomainRoutingThrowsExceptionWhenNotSupported()
	{
		$this->expectException(\RuntimeException::class);
		$router = new Router(new \Slim\Routing\RouteCollector(Factory::getResponseFactory(), new CallableResolver()));
		$router->domain(
			'example.com',
			function () {
			}
		);
	}
}