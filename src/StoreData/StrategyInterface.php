<?php

declare(strict_types=1);

namespace Scheduler\StoreData;

interface StrategyInterface
{
    /**
     * Initialize the strategy.
     */
    public function initialize(): void;

    /**
     * Retrieve the stored data.
     *
     * @return array The stored data.
     */
    public function read(): array;

    /**
     * Save the provided data.
     *
     * @param array $data The data to save.
     */
    public function write(array $data): void;

    /**
     * Check if the process is locked.
     *
     * @return bool True if the process is locked, false otherwise.
     */
    public function isLocked(): bool;

    /**
     * Lock the process to prevent concurrent execution.
     *
     * @return bool True if the lock is acquired, false otherwise.
     */
    public function acquireLock(): bool;

    /**
     * Release the lock.
     */
    public function releaseLock(): void;
}
