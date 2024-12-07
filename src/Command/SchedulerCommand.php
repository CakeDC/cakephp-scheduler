<?php

declare(strict_types=1);

namespace Scheduler\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use DateTime;
use DateInterval;
use Scheduler\StoreData\StrategyInterface;

/**
 * Scheduler Command.
 */
class SchedulerCommand extends Command
{
    protected StrategyInterface $storeStrategy;

    /**
     * @param ConsoleOptionParser $parser
     * @return ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        return $parser;
    }

    /**
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        // @todo remove later
        Configure::write('SchedulerShell.jobs', [
            'DumbJob' => [
                'interval' => 'next day 18:00',
                'task' => \App\Command\SchedulerTestCommand::class,
                'pass' => [
                    'arg1' => 'value1',
                    'arg2' => 'value2',
                ],
                'paused' => false,
            ],
        ]);

        $config = Configure::read('Scheduler.storeData');
        if (!$config) {
            $io->err(__('No configuration found for Scheduler!'));
            return Command::CODE_ERROR;
        }

        $strategyClass = $config['strategy'];
        if (!class_exists($strategyClass)) {
            $io->err(__('Strategy class `{0}` not found!', $strategyClass));
            return Command::CODE_ERROR;
        }

        $this->storeStrategy = new $strategyClass($config['options']);

        //$jobs = Configure::read('Scheduler.jobs') ?? [];
        $jobs = Configure::read('SchedulerShell.jobs') ?? []; // @todo recheck this later
        if (empty($jobs)) {
            $io->err(__('No jobs configured!'));
            return Command::CODE_ERROR;
        }

        $this->runJobs($jobs ?? [], $io);
        return Command::CODE_SUCCESS;
    }

    /**
     * @param array $jobs
     * @param ConsoleIo $io
     * @return void
     */
    private function runJobs(array $jobs, ConsoleIo $io): void
    {
        if ($this->storeStrategy->isLocked()) {
            $io->out(__('Another instance of the scheduler is running. Exiting.'));
            return;
        }

        try {
            $this->storeStrategy->acquireLock();
            $store = $this->storeStrategy->read();

            foreach ($jobs as $name => $job) {
                $now = new DateTime();

                if ($job['paused'] ?? false) {
                    $io->out(__('Skipping job: {0} (paused)', $name));
                    continue;
                }

                if (!isset($store[$name])) {
                    $store[$name] = $job;
                    $store[$name]['lastRun'] = null;
                }

                $lastRun = $store[$name]['lastRun'];
                if ($lastRun === null) {
                    $lastRun = new DateTime('1969-01-01 00:00:00');
                } else {
                    $lastRun = new DateTime($lastRun);
                }

                if (substr($job['interval'], 0, 1) === 'P') {
                    $lastRun->add(new DateInterval($job['interval']));
                } else {
                    $lastRun->modify($job['interval']);
                }

                if ($lastRun <= $now) {
                    $io->hr();
                    $io->out(__('Running job: {0}', $name));
                    $io->hr();

                    $store[$name]['lastResult'] = $this->executeCommand($job['task'], $job['pass'] ?? [], $io) ?? 0;
                    $store[$name]['lastRun'] = $now->format('Y-m-d H:i:s');
                } else {
                    $io->out(__('Skipping job: {0} (next run: {1})', $name, $lastRun->format('Y-m-d H:i:s')));
                }
            }

            $this->storeStrategy->write($store);
        } catch (\Exception $e) {
            $io->err($e->getMessage());
        } finally {
            $this->storeStrategy->releaseLock();
        }
    }
}
