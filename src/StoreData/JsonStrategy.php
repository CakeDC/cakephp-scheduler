<?php

declare(strict_types=1);

namespace Scheduler\StoreData;

use Cake\Core\InstanceConfigTrait;
use SplFileObject;
use RuntimeException;

class JsonStrategy implements StrategyInterface
{
    use InstanceConfigTrait;

    private string $storeFilePath;
    private string $lockedFilePath;

    protected $_defaultConfig = [
        'storePath' => TMP . 'scheduler',
        'storeFile' => 'cron_scheduler.json',
        'lockedFile' => 'cron_scheduler.locked',
        'processingTimeout' => 300,
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->initialize();
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $storePath = $this->getConfig('storePath');
        if (!is_dir($storePath) && !mkdir($storePath, 0755, true)) {
            throw new RuntimeException("Unable to create directory: {$storePath}");
        }

        $this->storeFilePath = $storePath . DS . $this->getConfig('storeFile');
        $this->lockedFilePath = $storePath . DS . $this->getConfig('lockedFile');

        if (!file_exists($this->storeFilePath)) {
            touch($this->storeFilePath);
        }
        if (!file_exists($this->lockedFilePath)) {
            touch($this->lockedFilePath);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(): array
    {
        $file = new SplFileObject($this->storeFilePath, 'r');
        if ($file->getSize() <= 0) {
            return [];
        }
        $data = $file->fread($file->getSize());

        return json_decode($data ?? [], true) ?? [];
    }

    /**
     * @inheritDoc
     */
    public function write(array $data): void
    {
        $file = new SplFileObject($this->storeFilePath, 'w');
        $file->fwrite(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @inheritDoc
     */
    public function isLocked(): bool
    {
        if (file_exists($this->lockedFilePath) && file_get_contents($this->lockedFilePath) === 'locked') {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function acquireLock(): bool
    {
        // @todo better change this for Cache::write('scheduler_lock', true, 'default');
        if (file_exists($this->lockedFilePath)) {
            $lastChange = filemtime($this->lockedFilePath);
            if ($lastChange !== false && (time() - $lastChange) < $this->getConfig('processingTimeout', 300)) {
                return false;
            }
        }
        file_put_contents($this->lockedFilePath, 'locked');
        return true;
    }

    /**
     * @inheritDoc
     */
    public function releaseLock(): void
    {
        if (file_exists($this->lockedFilePath)) {
            unlink($this->lockedFilePath);
        }
    }
}
