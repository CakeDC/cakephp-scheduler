<?php

declare(strict_types=1);

namespace Scheduler\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SchedulerStore Model
 *
 * @method \Scheduler\Model\Entity\SchedulerStore newEmptyEntity()
 * @method \Scheduler\Model\Entity\SchedulerStore newEntity(array $data, array $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[] newEntities(array $data, array $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore get($primaryKey, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Scheduler\Model\Entity\SchedulerStore[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SchedulerStoreTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('scheduler_store');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('interval_job')
            ->maxLength('interval_job', 255)
            ->requirePresence('interval_job', 'create')
            ->notEmptyString('interval_job');

        $validator
            ->scalar('task')
            ->maxLength('task', 255)
            ->requirePresence('task', 'create')
            ->notEmptyString('task');

        $validator
            ->scalar('pass')
            ->maxLength('pass', 255)
            ->requirePresence('pass', 'create')
            ->notEmptyString('pass');

        $validator
            ->dateTime('lastRun')
            ->requirePresence('lastRun', 'create')
            ->notEmptyDateTime('lastRun');

        $validator
            ->integer('lastResult')
            ->notEmptyString('lastResult');

        return $validator;
    }

    /**
     * @param Query $query
     * @param array $options
     * @return Query
     */
    public function findFormatArray(Query $query, array $options): Query
    {
        return $query->formatResults(function ($results) {
            return $results->combine('name', function ($row) {
                $row = $row->toArray();
                $row['pass'] = json_decode($row['pass'], true);
                $row['lastRun'] = $row['lastRun']?->format('Y-m-d H:i:s') ?? null;
                $row['interval'] = $row['interval_job'];
                unset($row['interval_job']);

                return $row;                
            });
        });
    }
}
