<?php

/**
 * @file
 * Contains Slim\Turbo\Routing\Cache\Memory
 */

namespace Slim\Turbo\Routing\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * SimpleCache memory based caching
 *
 * @NOTE    This is implemented here to prevent the need for another dependency
 *
 * @package Slim\Turbo\Provider
 */
class Memory
	implements CacheInterface
{
	/**
	 * @var array List of keys in memory
	 */
	protected $data = [];

	protected function validKey($key)
	{
		if (preg_match('/^[a-zA-Z0-9_.]+$/', (string)$key)) {
			return $key;
		}

		throw new \InvalidArgumentException("Key is not valid: {$key}");
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return $this->data[$this->validKey($key)] ?? $default;
	}

	public function set(string $key, mixed $value, $ttl = null): bool
	{
		$this->data[$this->validKey($key)] = $value;

		return true;
	}

	public function delete(string $key): bool
	{
		unset($this->data[$this->validKey($key)]);

		return true;
	}

	public function clear(): bool
	{
		$this->data = [];

		return true;
	}

	public function getMultiple(\Traversable|array $keys, mixed $default = null): \Traversable|array
	{
		$data = [];
		foreach ($keys as $key) {
			$data[$key] = $this->data[$this->validKey($key)] ?? $default;
		}

		return $data;
	}

	public function setMultiple(iterable $values, $ttl = null): bool
	{
		foreach ($values as $key => $value) {
			$this->data[$this->validKey($key)] = $value;
		}

		return true;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		foreach ($keys as $key) {
			unset($this->data[$this->validKey($key)]);
		}

		return true;
	}

	public function has(string $key): bool
	{
		return array_key_exists($this->validKey($key), $this->data);
	}
}