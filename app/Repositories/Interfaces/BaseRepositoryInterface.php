<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(): \Illuminate\Database\Eloquent\Collection;

    public function find(string $key, string $value): ?Model;

    public function findById(int $id): ?Model;

    public function create(array $data): Model;

    public function update(Model $model, array $data): bool;

    public function delete(int $id): bool;

    /**
     * Atomic update - safe for concurrent operations
     */
    public function atomicUpdate(int $id, array $data): bool;

    /**
     * Find and lock row for update (pessimistic locking)
     */
    public function findAndLock(int $id): ?Model;

    /**
     * Find by key and lock for update
     */
    public function findByKeyAndLock(string $key, string $value): ?Model;

    /**
     * Execute callback within a database transaction
     */
    public function transaction(callable $callback): mixed;

    /**
     * Atomic update with condition
     */
    public function atomicUpdateWhere(array $conditions, array $data): int;
}
