<?php

namespace Slim\Turbo\Routing\Cache;

use PHPUnit\Framework\TestCase;

class MemoryTest
	extends TestCase
{
	public function setUp(): void
	{
		$this->cache = new Memory();
	}

	public function testGetWithoutEntry()
	{
		self::assertEquals(null, $this->cache->get('test'));
	}

	public function testGetWithDefault()
	{
		self::assertEquals(4, $this->cache->get('test_default', 4));
	}

	public function testSet()
	{
		$this->cache->set('test', 2);
		self::assertEquals(2, $this->cache->get('test', 4));
	}

	public function testNonCriticalFunctionality()
	{
		$this->cache->set('test', 2);
		$this->cache->delete('test');
		self::assertFalse($this->cache->has('test'));
	}

	public function testMultipleFunctionality()
	{
		$this->cache->setMultiple(['t1' => 1, 't2' => 2]);
		self::assertEquals(['t1' => 1, 't3' => null], $this->cache->getMultiple(['t1', 't3']));
		self::assertEquals(['t1' => 1, 't3' => 'test'], $this->cache->getMultiple(['t1', 't3'], 'test'));
		$this->cache->deleteMultiple(['t1', 't3']);
		self::assertEquals(['t1' => null, 't2' => 2, 't3' => null], $this->cache->getMultiple(['t1', 't2', 't3']));
		$this->cache->clear();
		self::assertEquals(null, $this->cache->get('t2'));
	}

	public function testGetThrowsOnInvalidKey()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->cache->get('test key');
	}

	public function testDeleteThrowsOnInvalidKey()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->cache->delete('test key');
	}

	public function testHasThrowsOnInvalidKey()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->cache->has('test key');
	}

	public function testSetThrowsOnInvalidKey()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->cache->set('test key', 2);
	}
}