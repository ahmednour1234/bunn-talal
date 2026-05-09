<?php

namespace App\Repositories\Eloquent;

use App\Models\Collection;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionRepository extends BaseRepository implements CollectionRepositoryInterface
{
    public function __construct(Collection $model)
    {
        parent::__construct($model);
    }

    public function getById(int $id): Collection
    {
        return $this->model
            ->with([
                'customer:id,name,phone',
                'items.saleOrder:id,order_number,total,paid_amount',
            ])
            ->findOrFail($id);
    }

    public function getDelegateCollections(int $delegateId, ?int $tripId): EloquentCollection
    {
        $query = $this->model
            ->where('delegate_id', $delegateId)
            ->with([
                'customer:id,name,phone',
                'items.saleOrder:id,order_number,total,paid_amount',
            ]);

        if ($tripId !== null) {
            $query->where('trip_id', $tripId);
        }

        return $query->latest()->get();
    }

    public function create(array $data): Collection
    {
        return $this->model->create($data);
    }
}
