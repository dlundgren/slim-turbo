<?php

namespace Slim\Turbo\Test;

use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Turbo\Provider\RouteProvider;

class Routes
	implements RouteProvider
{
	public function register(RouteCollectorProxyInterface $router)
	{
		$router->get('/', 'test');
	}
}