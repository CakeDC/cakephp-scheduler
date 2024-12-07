<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Db\Action\AddColumn;

class CreateSchedulerStore extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('scheduler_store');
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('interval_job', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('task', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('pass', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('lastRun', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('lastResult', 'integer', [
            'default' => 0,
            'limit' => 1,
            'null' => false,
        ]);
        $table->AddColumn('paused', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->create();
    }
}
