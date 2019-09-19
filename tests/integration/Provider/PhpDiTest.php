<?php

namespace Slim\Turbo\Provider;

use DI\ContainerBuilder;
use function DI\get;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Turbo\Test\Routes;

class PhpDiTest
	extends TestCase
{
	public function setUp()
	{
		$this->builder = new ContainerBuilder();
		$this->builder->addDefinitions(PhpDi::definitions());
		$this->builder->addDefinitions(
			[
				RouteProvider::class => get(Routes::class)
			]
		);
	}

	public function testRegisterSlimProperly()
	{
		$di = $this->builder->build();
		self::assertInstanceOf(App::class, $di->get(App::class));
	}
}