<?php

namespace Slim\Turbo\Provider;

use DI\ContainerBuilder;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Turbo\Routing\DomainResolver;
use function DI\get;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Turbo\Test\Routes;

class PhpDiTest
	extends TestCase
{
	public function testRegisterSlimProperly()
	{
		$builder = new ContainerBuilder();
		$builder->addDefinitions(PhpDi::definitions());
		$builder->addDefinitions(
			[
				RouteProvider::class => get(Routes::class)
			]
		);
		$di = $builder->build();
		self::assertInstanceOf(App::class, $di->get(App::class));
	}

	public function testRegistersDomainRouting()
	{
		$builder = new ContainerBuilder();
		$builder->addDefinitions(PhpDi::definitions(true));
		$builder->addDefinitions(
			[
				RouteProvider::class => get(Routes::class)
			]
		);

		$di = $builder->build();
		self::assertInstanceOf(DomainResolver::class, $di->get(RouteResolverInterface::class));
	}
}