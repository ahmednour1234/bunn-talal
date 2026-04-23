<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->getById($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool
    {
        return $this->getById($id)->delete();
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        $query = $this->model->newQuery();
        if ($search && method_exists($this, 'applySearch')) {
            $query = $this->applySearch($query, $search);
        }
        return $query->latest()->paginate($perPage);
    }
}
