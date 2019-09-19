<?php

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem;
use Psr\SimpleCache\CacheInterface;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Provider\PhpDi;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

include dirname(__DIR__) . '/routes.php';

if (isset($_GET['cache'])) {
	if (file_exists($di = __DIR__ . '/cache/CompiledContainer.php')) {
		require $di;
		$container = new CompiledContainer();
	}
}

$filesystem = null;
if (isset($_GET['cache'])) {
	$filesystem = new FilesystemCachePool(
		new Flysystem\Filesystem(new Flysystem\Adapter\Local(__DIR__))
	);
}

if (isset($container)) {
	$container->set(RouteProvider::CACHE_KEY, $filesystem);
}
else {
	$dib = new \DI\ContainerBuilder;
	$dib->addDefinitions(PhpDi::definitions());
	$dib->addDefinitions(
		[
			RouteProvider::class => \DI\create(\App\Routes::class),
		]
	);
	if (isset($_GET['cache']) && !isset($container)) {
		$dib->addDefinitions(
			[
				CacheInterface::class    => $filesystem,
				RouteProvider::CACHE_KEY => $filesystem,
			]);
		$dib->enableCompilation(__DIR__);
	}
	$container = $dib->build();
}

$container->get(\Slim\App::class)->run();

