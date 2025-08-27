<?php

namespace Slim\Turbo;

use PHPUnit\Framework\TestCase;
use Slim\Interfaces\CallableResolverInterface;

class NullResolverTest
	extends TestCase
{
	protected CallableResolverInterface $resolver;

	protected function setUp(): void
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