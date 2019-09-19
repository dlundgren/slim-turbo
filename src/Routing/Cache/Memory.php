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
 * @NOTE This is implement here so as to prevent the need for another dependency
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
		if (preg_match('/^[a-zA-Z0-9_.]+$/', $key)) {
			return $key;
		}

		throw new \InvalidArgumentException("Key is not valid: {$key}");
	}

	public function get($key, $default = null)
	{
		return $this->data[$this->validKey($key)] ?? $default;
	}

	public function set($key, $value, $ttl = null)
	{
		$this->data[$this->validKey($key)] = $value;

		return true;
	}

	public function delete($key)
	{
		unset($this->data[$this->validKey($key)]);

		return true;
	}

	public function clear()
	{
		$this->data = [];

		return true;
	}

	public function getMultiple($keys, $default = null)
	{
		$data = [];
		foreach ($keys as $key) {
			$data[$key] = $this->data[$this->validKey($key)] ?? $default;
		}

		return $data;
	}

	public function setMultiple($values, $ttl = null)
	{
		foreach ($values as $key => $value) {
			$this->data[$this->validKey($key)] = $value;
		}

		return true;
	}

	public function deleteMultiple($keys)
	{
		foreach ($keys as $key) {
			unset($this->data[$this->validKey($key)]);
		}

		return true;
	}

	public function has($key)
	{
		return array_key_exists($this->validKey($key), $this->data);
	}
}