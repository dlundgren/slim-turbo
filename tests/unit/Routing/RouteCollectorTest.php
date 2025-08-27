<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Turbo\Exception\InvalidRoute;

class RouteCollectorTest
	extends TestCase
{
	/**
	 * @var RouteCollector
	 */
	protected $rc;

	protected function setUp(): void
	{
		$this->rc = new RouteCollector(
			Factory::getResponseFactory(),
			null,
			[
				'test' => ['/', 'test', []],
				'mw'   => ['/', 'test', ['mw']]
			]
		);
	}

	public function testGetNamedRouteUsesCache()
	{
		self::assertInstanceOf(Route::class, $this->rc->getNamedRoute('test'));
	}

	public function testGetNamedRouteThrowsException()
	{
		self::expectException(InvalidRoute::class);
		$this->rc->getNamedRoute('test_exception');
	}

	public function testLookupRouteUsesCache()
	{
		self::assertInstanceOf(Route::class, $this->rc->lookupRoute('test'));
	}

	public function testLookupRouteThrowsExceptionWhenMissingRoute()
	{
		self::expectException(InvalidRoute::class);
		$this->rc->lookupRoute('test_exception');
	}

	public function testMapReturnsCustomRouteObject()
	{
		self::assertInstanceOf(Route::class, $this->rc->map(['GET'], ' / ', 'test'));
	}

	public function testBuildRouteHandlesMiddleware()
	{
		$route = $this->rc->lookupRoute('mw');
		self::assertEquals(['mw'], $route->getMiddleware());
	}

	public function testDomainRoutingThrowsExceptionWhenCalledUnderGroup()
	{
		$this->expectException(\RuntimeException::class);
		$this->rc->group('throws', function ($router) {
			$router->domain('example.com', function ($router) {
				$router->get('test', 'dtest')->setName('dtest');
			});
		});
		$this->rc->getNamedRoute('dtest');
	}

	public function xtestDomainRouting()
	{
		$this->rc->domain('example.com', function ($router) {
			$router->group('throws', function ($router) {
				$r = $router->get('test', 'dtest');
				$r->setName('dtest');
			});
		});
		var_dump($this->rc);
		$route = $this->rc->getNamedRoute('dtest');
		self::assertEquals('example.com:throws/test', $route->getPattern());
	}
}