<?php declare(strict_types = 1);

namespace Tests\Contributte\Flysystem\Cases\DI;

/**
 * Test: DI\FlysystemExtension
 */

use Contributte\Flysystem\DI\FlysystemExtension;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use ReflectionProperty;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTmpDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addConfig(['parameters' => ['appDir' => Environment::getTmpDir() . '/cache']]);
		$compiler->addExtension('flysystem', new FlysystemExtension());
		$compiler->loadConfig(FileMock::create('
		services:
			baz: League\Flysystem\InMemory\InMemoryFilesystemAdapter()

		flysystem:
			filesystem:
				default:
					adapter: League\Flysystem\Local\LocalFilesystemAdapter(%appDir%/defaultStorage)
					autowired: true
				foo:
					adapter:
						type: League\Flysystem\Local\LocalFilesystemAdapter
						arguments:
							- %appDir%/fooStorage
				bar:
					adapter: League\Flysystem\InMemory\InMemoryFilesystemAdapter
					autowired: false
				baz:
					adapter: @baz
					autowired: false
', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class();

	/** @var Filesystem $filesystem */
	$filesystem = $container->getByType(Filesystem::class);
	$adapterReflectionProperty = new ReflectionProperty($filesystem, 'adapter');
	$adapterReflectionProperty->setAccessible(true);
	Assert::type(LocalFilesystemAdapter::class, $adapterReflectionProperty->getValue($filesystem));

	/** @var Filesystem $filesystem */
	$filesystem = $container->getService('flysystem.filesystem.foo');
	$adapterReflectionProperty = new ReflectionProperty($filesystem, 'adapter');
	$adapterReflectionProperty->setAccessible(true);
	Assert::type(LocalFilesystemAdapter::class, $adapterReflectionProperty->getValue($filesystem));

	/** @var Filesystem $filesystem */
	$filesystem = $container->getService('flysystem.filesystem.bar');
	$adapterReflectionProperty = new ReflectionProperty($filesystem, 'adapter');
	$adapterReflectionProperty->setAccessible(true);
	Assert::type(InMemoryFilesystemAdapter::class, $adapterReflectionProperty->getValue($filesystem));

	/** @var MountManager $mountManager */
	$mountManager = $container->getByType(MountManager::class);
	$filesystemReflectionProperty = new ReflectionProperty($mountManager, 'filesystems');
	$filesystemReflectionProperty->setAccessible(true);
	$filesystem = $filesystemReflectionProperty->getValue($mountManager)['default'];
	$adapterReflectionProperty = new ReflectionProperty($filesystem, 'adapter');
	$adapterReflectionProperty->setAccessible(true);
	Assert::type(LocalFilesystemAdapter::class, $adapterReflectionProperty->getValue($filesystem));
});
