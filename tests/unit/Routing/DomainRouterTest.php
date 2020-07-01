<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Routing\RoutingResults;

class DomainRouterTest
	extends TestCase
{
	public function testGroupUsesCorrectPattern()
	{
		$router = new DomainRouter(new RouteCollector(Factory::getResponseFactory()), 'test.example.com');
		$router->group(
			'/kakaw',
			function ($router) {
				$router->get('/moo', 'moo')->setName('km');
			}
		);

		$rc = $router->getRouteCollector();
		self::assertEquals('test.example.com:/kakaw/moo', $rc->getNamedRoute('km')->getPattern());
	}
}