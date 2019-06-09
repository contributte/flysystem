<?php declare(strict_types = 1);

/**
 * Test: DI\FlysystemExtension
 */

use Contributte\Flysystem\DI\FlysystemExtension;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\FileMock;
use Tests\Contributte\Flysystem\Fixtures\VoidPlugin;

require_once __DIR__ . '/../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addConfig(['parameters' => ['appDir' => CACHE_DIR]]);
		$compiler->addExtension('flysystem', new FlysystemExtension());
		$compiler->loadConfig(FileMock::create('
		services:
			baz: League\Flysystem\Adapter\NullAdapter()
		
		flysystem:
			filesystem:
				default:
					adapter: League\Flysystem\Adapter\Local(%appDir%/defaultStorage)
					autowired: true
					plugins:
						void1:
							type: Tests\Contributte\Flysystem\Fixtures\VoidPlugin
							arguments:
								- filesystemPlugin
				foo:
					adapter:
						type: League\Flysystem\Adapter\Local
						arguments:
							- %appDir%/fooStorage
				bar:
					adapter: League\Flysystem\Adapter\NullAdapter
					autowired: false
				baz:
					adapter: @baz
					autowired: false
			mountManager:
				plugins:
					void2: Tests\Contributte\Flysystem\Fixtures\VoidPlugin("mountManagerPlugin")
			plugins:
				void3: Tests\Contributte\Flysystem\Fixtures\VoidPlugin
', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class();

	/** @var Filesystem $filesystem */
	$filesystem = $container->getByType(Filesystem::class);
	Assert::type(Local::class, $filesystem->getAdapter());

	/** @var Filesystem $filesystem */
	$filesystem = $container->getService('flysystem.filesystem.foo');
	Assert::type(Local::class, $filesystem->getAdapter());

	/** @var Filesystem $filesystem */
	$filesystem = $container->getService('flysystem.filesystem.bar');
	Assert::type(NullAdapter::class, $filesystem->getAdapter());

	/** @var MountManager $mountManager */
	$mountManager = $container->getByType(MountManager::class);
	$filesystem = $mountManager->getFilesystem('default');
	Assert::type(Local::class, $filesystem->getAdapter());

	Assert::type(VoidPlugin::class, $container->getService('flysystem.filesystem.default.plugin.void1'));
	Assert::type(VoidPlugin::class, $container->getService('flysystem.mountManager.plugin.void2'));
	Assert::type(VoidPlugin::class, $container->getService('flysystem.plugin.void3'));
});
