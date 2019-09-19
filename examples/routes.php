<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Turbo\Provider\RouteProvider;

class Test
	implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		die("<pre>TestRoute: " . $request->getUri()->getPath() . '</pre>');
	}
}

class Routes
	implements RouteProvider
{
	public function register( $router)
	{
		$router->get('/', Test::class)->setName('home');
		$router->group('/kakaw', function($router) {
			$router->get('', Test::class);
			$router->get('/p', Test::class);
		});
	}
}