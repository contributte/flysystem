![](https://heatbadger.now.sh/github/readme/contributte/flysystem/)

<p align=center>
  <a href="https://github.com/contributte/flysystem/actions"><img src="https://badgen.net/github/checks/contributte/flysystem/master?cache=300"></a>
  <a href="https://coveralls.io/r/contributte/flysystem"><img src="https://badgen.net/coveralls/c/github/contributte/flysystem?cache=300"></a>
  <a href="https://packagist.org/packages/contributte/flysystem"><img src="https://badgen.net/packagist/dm/contributte/flysystem"></a>
  <a href="https://packagist.org/packages/contributte/flysystem"><img src="https://badgen.net/packagist/v/contributte/flysystem"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/flysystem"><img src="https://badgen.net/packagist/php/contributte/flysystem"></a>
  <a href="https://github.com/contributte/flysystem"><img src="https://badgen.net/github/license/contributte/flysystem"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Nette DI integration for [Flysystem](https://flysystem.thephpleague.com/), a filesystem abstraction library.

## Versions

| State       | Version     | Branch   | Nette  | PHP     |
|-------------|-------------|----------|--------|---------|
| dev         | `^0.4`      | `master` | `3.0+` | `>=8.2` |
| stable      | `^0.3`      | `master` | `3.0+` | `>=7.2` |

## Content

- [Usage](#usage)
- [Configuration](#configuration)
- [Implementation](#implementation)

## Usage

To install latest version of `contributte/flysystem` use [Composer](https://getcomposer.org/).

```bash
composer require contributte/flysystem
```

Register the extension in your NEON config:

```neon
extensions:
	flysystem: Contributte\Flysystem\DI\FlysystemExtension
```

## Configuration

```neon
flysystem:
	filesystem:
		default:
			adapter: League\Flysystem\Adapter\Local(%appDir%/../storage)
			autowired: true
			config: # $config parameter of League\Flysystem\Filesystem
				- disable_asserts: true
```

Minimal configuration is one filesystem with adapter, nothing more is required ;)

## Implementation

```php
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Nette\Application\UI\Presenter;

class FilePresenter extends Presenter
{

	/** @var Filesystem */
	private $filesystem;

	public function injectFilesystem(Filesystem $filesystem): void
	{
		$this->filesystem = $filesystem;
	}

	public function injectMountManager(MountManager $mountManager): void
	{
		// you can also get MountManager which has available all filesystems
		// it is not recommended and should be used only for transfer of files between filesystems
	}

	public function handleFileSave(): void
	{
		// get file and save its contents to $path
		$this->filesystem->write($path, $file->getContents());
	}

}
```

If you miss something here so just look at [League/Flysystem documentation](https://flysystem.thephpleague.com/docs/). This is only DI integration.

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package. This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
