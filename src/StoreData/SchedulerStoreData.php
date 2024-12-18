<?php

declare(strict_types=1);

namespace Scheduler\StoreData;

use Cake\Core\Configure;
use Scheduler\StoreData\StrategyInterface;

class SchedulerStoreData
{
    /**
     * @var StrategyInterface
     */
    protected static StrategyInterface $storeData;

    /**
     * @param array $options
     * @return StrategyInterface
     * @throws \RuntimeException
     */
    public static function get(array $options = []): StrategyInterface
    {
        if (!empty(self::$storeData)) {
            return self::$storeData;
        }

        $config = Configure::read('Scheduler.storeData');
        if (!$config) {
            throw new \RuntimeException(__('No configuration found for Scheduler!'));
        }

        $strategyClass = $config['strategy'];
        if (!class_exists($strategyClass) && is_subclass_of($strategyClass, StrategyInterface::class)) {
            throw new \RuntimeException(__('Strategy class `{0}` not found!', $strategyClass));
        }

        $options = array_merge($config['options'] ?? [], $options);
        self::$storeData = new $strategyClass($options);

        return self::$storeData;
    }
}
