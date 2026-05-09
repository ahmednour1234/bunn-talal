<?php

namespace App\Repositories\Contracts;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface CollectionRepositoryInterface
{
    public function getById(int $id): Collection;

    public function getDelegateCollections(int $delegateId, ?int $tripId): EloquentCollection;

    public function create(array $data): Collection;
}
