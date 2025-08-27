<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;

class CachedCollectorTest
	extends TestCase
{
	protected $collector;

	protected function setUp(): void
	{
		$this->collector = new CachedCollector(Factory::getResponseFactory());
		$this->collector->map(['GET'], '/1', 'test1')->setName('test1');
		$this->collector->map(['GET'], '/2', 'test2')->setName('test2');
	}

	public function testBuild()
	{
		[$namedRoutes, $dispatchData] = $this->collector->build();
		self::assertEquals(['test1' => ['/1', 'test1', []], 'test2' => ['/2', 'test2', []]], $namedRoutes);
		self::assertEquals([['GET' => ['/1' => 'test1', '/2' => 'test2']], []], $dispatchData);
	}

	public function testBuildOnlyGeneratesOnce()
	{
		[$nr1, $dd1] = $this->collector->build();
		[$nr2, $dd2] = $this->collector->build();
		self::assertSame($nr1, $nr2);
		self::assertSame($dd1, $dd2);
	}
}