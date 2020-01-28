<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;

class RouterTest
	extends TestCase
{
	/**
	 * @var Router
	 */
	protected $router;

	public function setUp()
	{
		$this->router = new Router(
			new RouteCollector(Factory::getResponseFactory()),
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

		self::assertEquals('t/h', $route->getPattern());
	}
}