<?php

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem;
use Psr\SimpleCache\CacheInterface;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Provider\Symfony;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

include dirname(__DIR__) . '/routes.php';

if (isset($_GET['cache']) && file_exists($file = __DIR__ . '/cache/CompiledContainer.php')) {
	require $file;
	$container = new CompiledContainer();
}

if (!isset($container)) {
	$container = new ContainerBuilder();
	$container->registerExtension($ext = new Symfony());
	$container->loadFromExtension($ext->getAlias());
	$container->register(RouteProvider::class, \App\Routes::class);
	$container->register(\App\Test::class)->setPublic(true);

	// set up the caching system
	$container->register(Flysystem\Adapter\Local::class)
			  ->setArgument('$root', __DIR__);
	$container->register(Flysystem\Filesystem::class)
			  ->setArgument('$adapter', new Reference(Flysystem\Adapter\Local::class));
	$container->register(CacheInterface::class, FilesystemCachePool::class)
			  ->setArgument('$filesystem', new Reference(Flysystem\Filesystem::class))
			  ->setPublic(true);

	$container->setAlias(RouteProvider::CACHE_KEY, CacheInterface::class);
	$container->compile();

	if (isset($_GET['cache'])) {
		file_put_contents($file, (new PhpDumper($container))->dump(['class' => 'CompiledContainer']));
	}
}


$container->get(\Slim\App::class)->run();
