<?php declare(strict_types = 1);

/**
 * Test: DI\FlysystemExtension
 */

use Contributte\Flysystem\DI\FlysystemExtension;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addConfig(['parameters' => ['appDir' => CACHE_DIR]]);
		$compiler->addExtension('flysystem', new FlysystemExtension());
		$compiler->loadConfig(FileMock::create('
		flysystem:
			filesystem:
				default:
					adapter: League\Flysystem\Adapter\Local(%appDir%/defaultStorage)
					autowired: true
				foo:
					adapter:
						type: League\Flysystem\Adapter\Local
						arguments:
							- %appDir%/fooStorage
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

	/** @var MountManager $mountManager */
	$mountManager = $container->getByType(MountManager::class);
	$filesystem = $mountManager->getFilesystem('default');
	Assert::type(Local::class, $filesystem->getAdapter());
});
