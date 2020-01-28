<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;

class RouteCollectorTest
	extends TestCase
{
	/**
	 * @var RouteCollector
	 */
	protected $rc;

	public function setUp()
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
		self::expectException(\RuntimeException::class);
		$this->rc->getNamedRoute('test_exception');
	}

	public function testLookupRouteUsesCache()
	{
		self::assertInstanceOf(Route::class, $this->rc->lookupRoute('test'));
	}

	public function testLookupRouteThrowExceptionWhenMissingRoute()
	{
		self::expectException(\RuntimeException::class);
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
}