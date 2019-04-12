<?php declare(strict_types = 1);

namespace Contributte\Flysystem\DI;

use Contributte\Flysystem\Exception\Logic\InvalidStateException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Utils\Strings;

class FlysystemExtension extends CompilerExtension
{

	/** @var mixed[] */
	private $defaults = [
		'filesystem' => [],
		'mountManager' => [
			'plugins' => [],
		],
		'plugins' => [],
	];

	/** @var mixed[] */
	private $filesystemDefaults = [
		'adapter' => null,
		'config' => null,
		'plugins' => [],
		'autowired' => false,
	];

	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();
		$filesystemsDefinitions = [];

		// Register filesystems
		foreach ($config['filesystem'] as $name => $args) {
			$filesystemName = 'filesystem.' . $name;
			$adapterName = $filesystemName . '.adapter';

			$args = $this->validateConfig($this->filesystemDefaults, $args, $this->prefix($filesystemName));

			if ($args['adapter'] === null) {
				throw new InvalidStateException(sprintf('%s must be defined', $this->prefix($adapterName)));
			}

			// Register adapter same way as service (setup, arguments, type etc.)
			if (!is_string($args['adapter']) || !Strings::startsWith($args['adapter'], '@')) {
				if (!method_exists($this, 'loadDefinitionsFromConfig')) {
					$processor = $builder->addDefinition($this->prefix($adapterName))
						->setAutowired(false);

					Compiler::loadDefinition($processor, $args['adapter']);
				} else {
					// Nette v3 compatibility
					$this->loadDefinitionsFromConfig(
						[
							$adapterName => $args['adapter'],
						]
					);
				}
				$args['adapter'] = '@' . $this->prefix($adapterName);
			}

			$filesystemsDefinitions[$name] = $filesystem = $builder->addDefinition($this->prefix($filesystemName))
				->setType(Filesystem::class)
				->setArguments(
					[
						$args['adapter'],
						$args['config'] ?? null,
					]
				);

			if ($args['autowired'] !== true) {
				$filesystem->setAutowired(false);
			}

			foreach ($config['plugins'] as $plugin) {
				$filesystem->addSetup('addPlugin', [$plugin]);
			}

			foreach ($args['plugins'] as $plugin) {
				$filesystem->addSetup('addPlugin', [$plugin]);
			}
		}

		// Register mount manager
		$mountManager = $builder->addDefinition($this->prefix('mountManager'))
			->setType(MountManager::class)
			->setArguments([$filesystemsDefinitions]);

		foreach ($config['plugins'] as $plugin) {
			$mountManager->addSetup('addPlugin', [$plugin]);
		}

		foreach ($config['mountManager']['plugins'] as $plugin) {
			$mountManager->addSetup('addPlugin', [$plugin]);
		}
	}

}
