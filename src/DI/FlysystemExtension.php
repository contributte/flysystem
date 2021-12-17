<?php declare(strict_types = 1);

namespace Contributte\Flysystem\DI;

use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class FlysystemExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'filesystem' => Expect::arrayOf(Expect::structure([
				'adapter' => Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class)),
				'config' => Expect::array(),
				'plugins' => Expect::arrayOf(
					Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))
				),
				'autowired' => Expect::bool(false),
			])),
			'mountManager' => Expect::structure([
				'plugins' => Expect::arrayOf(
					Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))
				),
			]),
			'plugins' => Expect::arrayOf(
				Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))
			),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;
		$definitionHelper = new ExtensionDefinitionsHelper($this->compiler);

		$filesystemsDefinitions = [];

		// Register filesystems
		foreach ($config->filesystem as $filesystemName => $filesystemConfig) {
			$filesystemPrefix = $this->prefix('filesystem.' . $filesystemName);

			$adapterPrefix = $filesystemPrefix . '.adapter';
			$adapterDefinition = $definitionHelper->getDefinitionFromConfig($filesystemConfig->adapter, $adapterPrefix);
			if ($adapterDefinition instanceof Definition) {
				$adapterDefinition->setAutowired(false);
			}

			$filesystemsDefinitions[$filesystemName] = $filesystem = $builder->addDefinition($filesystemPrefix)
				->setType(Filesystem::class)
				->setArguments(
					[
						$adapterDefinition,
						$filesystemConfig->config,
					]
				);

			if (!$filesystemConfig->autowired) {
				$filesystem->setAutowired(false);
			}
		}

		// Register mount manager
		$mountManagerPrefix = $this->prefix('mountManager');
		$builder->addDefinition($mountManagerPrefix)
			->setType(MountManager::class)
			->setArguments([$filesystemsDefinitions]);
	}

}
