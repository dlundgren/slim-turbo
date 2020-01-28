<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Routing\FastRouteDispatcher;
use Slim\Routing\RoutingResults;

class DispatcherTest
	extends TestCase
{
	public function setUp()
	{
		$this->rc = new RouteCollector(Factory::getResponseFactory());
	}

	public function testDispatch()
	{
		$frd = $this->getMockBuilder(FastRouteDispatcher::class)
					->disableOriginalConstructor()
					->getMock();
		$frd->expects($this->once())->method('dispatch')->willReturn([FastRouteDispatcher::NOT_FOUND, 't1', ['t2']]);

		$d       = new Dispatcher($this->rc, $frd);
		$results = $d->dispatch('GET', '/');

		self::assertInstanceOf(RoutingResults::class, $results);
	}

	public function testGetAllowedMethods()
	{
		$frd = $this->getMockBuilder(FastRouteDispatcher::class)
					->disableOriginalConstructor()
					->getMock();
		$frd->expects($this->once())->method('getAllowedMethods')->willReturn(['TEST']);

		self::assertEquals(['TEST'], (new Dispatcher($this->rc, $frd))->getAllowedMethods('/'));
	}
}