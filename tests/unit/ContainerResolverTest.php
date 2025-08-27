<?php

namespace Slim\Turbo;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Slim\Turbo\Test\TestController;

class ContainerResolverTest
	extends TestCase
{
	/**
	 * @var Container
	 */
	protected $container;

	protected $resolver;

	protected function setUp(): void
	{
		$this->container = new Container();
		$this->resolver  = new ContainerResolver($this->container);
	}

	public function testResolvesFromContainer()
	{
		$obj = new \stdClass();
		$this->container->set('test', $obj);

		self::assertInstanceOf(ContainerResolver::class, $this->resolver->resolve('test'));
	}

	public function testThrowsWhenInvoked()
	{
		$this->expectException(\RuntimeException::class);
		($this->resolver)();
	}

	public function testResolveUsesCallableResolver()
	{
		$this->container->set(TestController::class, new TestController());

		self::assertInstanceOf(ContainerResolver::class, $this->resolver->resolve(TestController::class . ':get'));
	}
}