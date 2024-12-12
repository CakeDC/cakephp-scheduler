<?php
declare(strict_types=1);

namespace Scheduler\Model\Entity;

use Cake\ORM\Entity;

/**
 * SchedulerStore Entity
 *
 * @property int $id
 * @property string $name
 * @property string $interval_job
 * @property string $task
 * @property string $pass
 * @property \Cake\I18n\FrozenTime $lastRun
 * @property int $lastResult
 * @property bool $paused
 */
class SchedulerStore extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'name' => true,
        'interval_job' => true,
        'task' => true,
        'pass' => true,
        'lastRun' => true,
        'lastResult' => true,
        'paused' => true,
        'lastGenDate' => true,
    ];

    protected $_virtual = ['pass_array'];

    protected function _getPassArray(): array
    {
        return json_decode($this->pass ?? '[]', true) ?? [];
    }
}
