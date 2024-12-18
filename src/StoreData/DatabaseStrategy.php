<?php

declare(strict_types=1);

namespace Scheduler\StoreData;

use Cake\Cache\Cache;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class DatabaseStrategy implements StrategyInterface
{
    use InstanceConfigTrait;

    protected Table $SchedulerStore;

    protected $_defaultConfig = [
        'tableClass' => \Scheduler\Model\Table\SchedulerStoreTable::class,
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
        $this->SchedulerStore = TableRegistry::getTableLocator()->get($this->getConfig('tableClass'));
    }

    /**
     * @inheritDoc
     */
    public function read(): array
    {
        return $this->SchedulerStore->find('formatArray')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function write(array $data): void
    {
        $primaryKey = $this->SchedulerStore->getPrimaryKey();
        $entities = [];
        foreach ($data as $key => $row) {
            $entity = $this->SchedulerStore->find()
                ->where(['OR' => [
                    [$this->SchedulerStore->aliasField('name') . ' IS' => $key],
                    [$this->SchedulerStore->aliasField($primaryKey) . ' IS' => $row[$primaryKey]],
                ]])
                ->first();

            if (!empty($row['pass'])) {
                $row['pass'] = json_encode($row['pass']);
            }
            if (empty($row['name'])) {
                $row['name'] = $key;
            }
            if (empty($row['interval_job'])) {
                $row['interval_job'] = $row['interval'];
            }
            if ($entity) {
                $entity = $this->SchedulerStore->patchEntity($entity, $row);
            } else {
                $entity = $this->SchedulerStore->newEntity($row);
            }
            $entities[] = $entity;
        }

        $this->SchedulerStore->saveManyOrFail($entities);
    }

    /**
     * @inheritDoc
     */
    public function isLocked(): bool
    {
        return Cache::read('scheduler_lock', 'default') === true;
    }

    /**
     * @inheritDoc
     */
    public function acquireLock(): bool
    {
        return Cache::write('scheduler_lock', true, 'default');
    }

    /**
     * @inheritDoc
     */
    public function releaseLock(): void
    {
        Cache::delete('scheduler_lock', 'default');
    }
}
