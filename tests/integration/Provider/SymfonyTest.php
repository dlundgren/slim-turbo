<?php

namespace Slim\Turbo\Provider;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Middleware\RoutingMiddleware;
use Slim\Turbo\Middleware\DomainRouting;
use Slim\Turbo\Test\Cachier;
use Slim\Turbo\Test\Routes;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SymfonyTest
	extends TestCase
{
	public function setUp()
	{
		$this->builder = new ContainerBuilder();
		$this->builder->registerExtension($ext = new Symfony());
		$this->builder->loadFromExtension($ext->getAlias());
	}

	public function testRegisterSlimProperly()
	{
		$this->builder->register(RouteProvider::class, Routes::class);
		$this->builder->compile();

		self::assertInstanceOf(App::class, $this->builder->get(App::class));
	}

	public function testWithCustomRouteProvider()
	{
		$builder = new ContainerBuilder();
		$builder->registerExtension($ext = new Symfony());
		$builder->loadFromExtension($ext->getAlias(), ['route_provider' => Routes::class]);
		$builder->compile();

		self::assertInstanceOf(App::class, $builder->get(App::class));
	}

	public function testRegisterWithCustomCache()
	{
		$builder = new ContainerBuilder();
		$builder->registerExtension($ext = new Symfony());
		$builder->loadFromExtension($ext->getAlias(), ['route_cache' => Cachier::class]);
		$builder->register(RouteProvider::class, Routes::class);
		$builder->register(Cachier::class);
		$builder->compile();

		self::assertInstanceOf(App::class, $builder->get(App::class));
	}

	public function testRegistersDomainRouting()
	{
		$builder = new ContainerBuilder();
		$builder->registerExtension($ext = new Symfony());
		$builder->loadFromExtension($ext->getAlias(), ['route_cache' => Cachier::class]);
		$builder->setParameter(Symfony::DOMAIN_ROUTING_PARAMETER, true);
		$builder->register(RouteProvider::class, Routes::class);
		$builder->register(Cachier::class);
		$builder->compile();

		self::assertInstanceOf(DomainRouting::class, $builder->get(RoutingMiddleware::class));
	}
}