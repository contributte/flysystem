# Contributte Flysystem

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Implementation](#implementation)

## Setup

```bash
composer require contributte/flysystem
```

```yaml
extensions:
    flysystem: Contributte\Flysystem\DI\FlysystemExtension
```
## Configuration

```yaml
flysystem:
    filesystem:
        default:
            adapter: League\Flysystem\Adapter\Local(%appDir%/../storage)
            autowired: true
            config: # $config parameter of League\Flysystem\Filesystem
                - disable_asserts: true
            plugins:
                - Your\Filesystem\Plugin()
    mountManager:
        plugins:
            - Your\MountManager\Plugin()
    plugins: # plugins for all filesystems and mount manager
        - Your\EverywhereUsed\Plugin()
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

If you miss something here so just look at [League/Flysystem documentation](http://flysystem.thephpleague.com/docs/). This is only DI integration.
