<?php

namespace Slim\Turbo;

use DI\Container;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteParser;
use Slim\Routing\RouteResolver;
use Slim\Routing\RouteRunner;
use Slim\Turbo\Routing\RouteCollector;
use Slim\Turbo\Test\RoutedMiddleware;

class MiddlewareDispatcherTest
	extends TestCase
{
	const DEFAULT_TEST_STATUS_CODE = 200;

	public function setUp()
	{
		$this->di = new Container();

		// @formatter:off
		$this->di->set(
			't1',
			new class extends \stdClass {
				public $name = 't1';
			}
		);
		$this->di->set(
			't2',
			new class extends \stdClass {
				public $name = 't2';
			}
		);
		$this->di->set(
			't3',
			new class extends \stdClass{
				public $name = 't3';
			}
		);
		$this->di->set(
			't4',
			new class extends \stdClass{
				public $name = 't4';
			}
		);
		// @formatter:on

		$this->dispatcher = new MiddlewareDispatcher($this->di);
		$this->dispatcher->seedMiddlewareStack(
			new class
				implements RequestHandlerInterface {
				public function handle(ServerRequestInterface $request): ResponseInterface
				{
					return Factory::createResponse(MiddlewareDispatcherTest::DEFAULT_TEST_STATUS_CODE);
				}
			}
		);
	}

	public function buildContainer()
	{
		$container = new Container();

		return $container;
	}

	public function testFromConstructor()
	{
		$mw         = ['t1', 't4'];
		$dispatcher = new MiddlewareDispatcher($this->di, $mw);

		self::assertEquals($mw, $dispatcher->getMiddleware());
	}

	public function testSeedMiddlewareStackThrowsExceptionWhenMissingRouteRunner()
	{
		$dispatcher = new MiddlewareDispatcher();
		$this->expectException(\RuntimeException::class);
		$dispatcher->seedMiddlewareStack(
			new RouteRunner(
				$this->createMock(RouteResolver::class),
				$this->createMock(RouteParser::class)
			)
		);
	}

	public function testAddsWithoutResolving()
	{
		$this->dispatcher->add('t1');

		self::assertEquals(['t1'], $this->dispatcher->getMiddleware());
	}

	public function testAddsFromArray()
	{
		$this->dispatcher->add(['t1', 't2']);

		self::assertEquals(['t1', 't2'], $this->dispatcher->getMiddleware());
	}

	public function testHandleResolves()
	{
		$this->di->set(
			't5',
			new class
				implements MiddlewareInterface {
				public function process(
					ServerRequestInterface $request,
					?RequestHandlerInterface $handler = null
				): ResponseInterface {
					return Factory::createResponse(333);
				}
			}
		);

		$this->dispatcher->add('t5');
		self::assertEquals(333, $this->dispatcher->handle(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testSeedMiddlewareStackUsesContainerRouteRunner()
	{
		$this->di->set(
			\Slim\Turbo\Routing\RouteRunner::class,
			new class
				implements RequestHandlerInterface {
				public function handle(ServerRequestInterface $request): ResponseInterface
				{
					return Factory::createResponse(333);
				}
			}
		);

		$dispatcher = new MiddlewareDispatcher($this->di);
		$collector  = new RouteCollector(Factory::getResponseFactory());
		$dispatcher->seedMiddlewareStack(
			new RouteRunner(
				new RouteResolver($collector),
				new RouteParser($collector)
			)
		);
		$response = $dispatcher->handle(Factory::createServerRequest('GET', '/'));
		self::assertEquals(333, $response->getStatusCode());
	}

	public function testHandlesCallableForDevelopment()
	{
		$test = function () {
			return Factory::createResponse(222);
		};

		$this->dispatcher->add($test);

		self::assertEquals(222, $this->dispatcher->handle(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testHandlesMiddlewareInstance()
	{
		$test = new class
			implements MiddlewareInterface {
			public function process(
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				return Factory::createResponse(222);
			}
		};

		$this->dispatcher->add($test);

		self::assertEquals(222, $this->dispatcher->handle(Factory::createServerRequest('GET', '/'))->getStatusCode());
	}

	public function testResolveMiddlewareThrowsExceptionOnBadMiddleware()
	{
		$this->dispatcher->add(new \stdClass());

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Middleware');

		$this->dispatcher->handle(Factory::createServerRequest('GET', '/'));
	}

	public function testResolveMiddlewareWithParametersThrowsException()
	{
		$test = new class
			implements MiddlewareInterface {
			public function process(
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				return Factory::createResponse(222);
			}
		};

		$this->dispatcher->add($test, 'bad');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Cannot pass parameters to non-ParameterAware Middleware.');

		$this->dispatcher->handle(Factory::createServerRequest('GET', '/'));
	}

	public function testResolveMiddlewareHonorsCallable()
	{
		$test = function (...$args) use (&$routed) {
			return $routed = (new RoutedMiddleware())->withParameters($args);
		};

		$this->dispatcher->add($test, 'bad');
		$this->dispatcher->handle(Factory::createServerRequest('GET', '/'));
		self::assertEquals(['bad'], $routed->parameters());
	}
}