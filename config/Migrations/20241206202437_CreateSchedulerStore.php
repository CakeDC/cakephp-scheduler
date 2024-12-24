<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSchedulerStore extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('scheduler_store');
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'Name of the job',
        ]);
        $table->addColumn('interval_job', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'Interval of the job',
        ]);
        $table->addColumn('task', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'Task to run, src/Job/Service/*',
        ]);
        $table->addColumn('pass', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'comment' => 'Parameters to pass to the task',
        ]);
        $table->addColumn('lastRun', 'datetime', [
            'default' => null,
            'null' => false,
            'comment' => 'Last time the job was run',
        ]);
        $table->addColumn('lastResult', 'integer', [
            'default' => 0,
            'limit' => 1,
            'null' => false,
            'comment' => 'Last result of the job, 0 = success, 1 = failure',
        ]);
        $table->AddColumn('paused', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Is the job paused',
        ]);
        $table->create();
    }
}
