<?php declare(strict_types = 1);

namespace Tests\Contributte\Flysystem\Fixtures;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class VoidPlugin implements PluginInterface
{

	/** @var string */
	private $method;

	public function __construct(string $method = 'void')
	{
		$this->method = $method;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function setFilesystem(FilesystemInterface $filesystem): void
	{
	}

	/**
	 * @param mixed $foo
	 */
	public function handle($foo): void
	{
	}

}
