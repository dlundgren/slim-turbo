<?php

namespace Slim\Turbo\Routing\Cache;

use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Routing\CachedCollector;

class FactoryTest
	extends TestCase
{
	protected $provider;

	protected function setUp(): void
	{
		$this->provider = new class
			implements RouteProvider {
			public $added = 0;

			protected $routes = [
				['/', 'test']
			];

			public function add(...$args)
			{
				$this->routes[] = $args;
			}

			public function register(RouteCollectorProxyInterface $router)
			{
				foreach ($this->routes as $args) {
					$this->added++;
					$router->get(...$args);
				}
			}
		};
	}

	public function testBuild()
	{
		self::assertEquals(['route0' => ['/', 'test', []]], Factory::build(Factory::NAMED_ROUTES, $this->provider));
		self::assertEquals(1, $this->provider->added);
	}

	public function testBuildUsesCustomCache()
	{
		$this->provider->add('/t', 'test');
		$routes = [
			'route0' => ['/', 'test', []],
			'route1' => ['/t', 'test', []]
		];
		self::assertEquals($routes, Factory::build(Factory::NAMED_ROUTES, $this->provider, $cache = new Memory()));
		self::assertEquals(2, $this->provider->added);
	}

	public function testBuildReturnsFromCache()
	{
		$cache = new Memory();
		$cache->set(Factory::NAMED_ROUTES, [1]);

		self::assertEquals([1], Factory::build(Factory::NAMED_ROUTES, $this->provider, $cache));
		self::assertEquals(0, $this->provider->added);
	}

	public function testBuildUsesProvidedCacheCollector()
	{
		$collector = $this->createMock(CachedCollector::class);
		$collector->expects($this->once())->method('build')->willReturn([[], []]);

		Factory::build(Factory::NAMED_ROUTES, $this->provider, new Memory, $collector);
	}
}