<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(string $key, string $value): ?Model
    {
        return $this->model->where($key, $value)->first();
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id) > 0;
    }

    public function atomicUpdate(int $id, array $data): bool
    {
        return $this->model->where('id', $id)
            ->update($data) > 0;
    }

    public function findAndLock(int $id): ?Model
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }

    public function findByKeyAndLock(string $key, string $value): ?Model
    {
         return $this->model->where($key, $value)->lockForUpdate()->first();
    }

    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    public function atomicUpdateWhere(array $conditions, array $data): int
    {
        return $this->model->where($conditions)->update($data);
    }
}
