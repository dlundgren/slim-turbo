<?php

namespace Slim\Turbo;

use PHPUnit\Framework\TestCase;

class NullResolverTest
	extends TestCase
{
	protected $resolver;

	public function setUp()
	{
		$this->resolver = new NullResolver();
	}

	public function testResolvesFromContainer()
	{
		self::assertInstanceOf(NullResolver::class, $this->resolver->resolve('test'));
	}

	public function testThrowsWhenInvoked()
	{
		$this->expectException(\RuntimeException::class);
		($this->resolver)();
	}
}