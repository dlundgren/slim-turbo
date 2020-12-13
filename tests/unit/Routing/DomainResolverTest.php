<?php

namespace Slim\Turbo\Routing;

use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Slim\Routing\RoutingResults;

class DomainResolverTest
	extends TestCase
{
	protected $routeCollector;

	public function setUp()
	{
		$this->router = new Router(
			$this->routeCollector = new CachedCollector(Factory::getResponseFactory()),
			'/'
		);

		$this->router->get('v1', 'main_v1')->setName('main-v1');
		$this->router->domain(
			'api',
			function ($router) {
				$router->get('/v1', 'api_v1')->setName('sub-v1');
			}
		);
		$this->router->domain(
			'api.example.com',
			function ($router) {
				$router->get('/v1', 'api_v1')->setName('full-v1');
			}
		);
	}

	public function testResolveRouteFromUri()
	{
		list($resolver, $result) = $this->runResolverWithArgs($this->routeCollector);
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
		self::assertEquals('sub-v1', $resolver->resolveRoute($result->getRouteIdentifier())->getName());
	}

	public function testResolveRouteFromUriWithFullDomain()
	{
		list($resolver, $result) = $this->runResolverWithArgs($this->routeCollector, null, false);
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
		self::assertEquals('full-v1', $resolver->resolveRoute($result->getRouteIdentifier())->getName());
	}

	public function provideIgnoredHostPayload()
	{
		return [
			['example.com'],
			['www.example.com'],
			['api.example.com']
		];
	}
	/**
	 * @dataProvider provideIgnoredHostPayload
	 */
	public function testResolveRouteFromUriWithIgnoredHost($host)
	{
		$resolver = new DomainResolver($this->routeCollector, null, true, ['api.example.com']);
		$request  = Factory::createServerRequest('GET', '/v1');
		$request  = $request->withUri($request->getUri()->withHost($host));
		$result   = $resolver->resolveRouteFromUri($request->getUri(), 'GET');
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
		self::assertEquals('main-v1', $resolver->resolveRoute($result->getRouteIdentifier())->getName());
	}

	public function testComputeRoutingResultsWithoutStartingSlash()
	{
		$resolver = new DomainResolver($this->routeCollector);
		$result = $resolver->computeRoutingResults('v1', 'GET');
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
		self::assertEquals('main-v1', $resolver->resolveRoute($result->getRouteIdentifier())->getName());
	}

	protected function runResolverWithArgs(...$args)
	{
		$resolver = new DomainResolver(...$args);
		$request  = Factory::createServerRequest('GET', '/v1');
		$request  = $request->withUri($request->getUri()->withHost('api.example.com'));

		return [$resolver, $resolver->resolveRouteFromUri($request->getUri(), 'GET')];
	}

	public function testLocalhostIsIgnoredByDefault()
	{
		$resolver = new DomainResolver($this->routeCollector);
		$request  = Factory::createServerRequest('GET', '/v1');
		$request  = $request->withUri($request->getUri()->withHost('localhost'));

		$result = $resolver->resolveRouteFromUri($request->getUri(), 'GET');
		self::assertEquals(RoutingResults::FOUND, $result->getRouteStatus());
	}

	public function testLocalhostIsNotIgnoredUnlessSpecified()
	{
		$resolver = new DomainResolver($this->routeCollector, null, true, []);
		$request  = Factory::createServerRequest('GET', '/v1');
		$request  = $request->withUri($request->getUri()->withHost('localhost'));

		$result = $resolver->resolveRouteFromUri($request->getUri(), 'GET');
		self::assertEquals(RoutingResults::NOT_FOUND, $result->getRouteStatus());
	}
}