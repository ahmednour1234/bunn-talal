<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;

class UnitService
{
    public function __construct(protected UnitRepositoryInterface $unitRepository)
    {
    }

    public function getAllUnits()
    {
        return $this->unitRepository->getAll();
    }

    public function getUnitById(int $id)
    {
        return $this->unitRepository->getById($id);
    }

    public function createUnit(array $data)
    {
        return $this->unitRepository->create($data);
    }

    public function updateUnit(int $id, array $data)
    {
        return $this->unitRepository->update($id, $data);
    }

    public function deleteUnit(int $id): bool
    {
        return $this->unitRepository->delete($id);
    }

    public function paginateUnits(int $perPage = 15, ?string $search = null)
    {
        return $this->unitRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $unit = $this->unitRepository->getById($id);
        $unit->update(['is_active' => !$unit->is_active]);
        return $unit;
    }

    public function getBaseUnitsByType(string $type)
    {
        return Unit::where('type', $type)->whereNull('base_unit_id')->where('is_active', true)->get();
    }

    public function getUnitsByType(string $type)
    {
        return Unit::where('type', $type)->where('is_active', true)->get();
    }
}
