<?php

namespace App\Services;

use App\Repositories\Contracts\AreaRepositoryInterface;

class AreaService
{
    public function __construct(protected AreaRepositoryInterface $areaRepository)
    {
    }

    public function getAllAreas()
    {
        return $this->areaRepository->getAll();
    }

    public function getAreaById(int $id)
    {
        return $this->areaRepository->getById($id);
    }

    public function createArea(array $data)
    {
        return $this->areaRepository->create($data);
    }

    public function updateArea(int $id, array $data)
    {
        return $this->areaRepository->update($id, $data);
    }

    public function deleteArea(int $id): bool
    {
        return $this->areaRepository->delete($id);
    }

    public function paginateAreas(int $perPage = 15, ?string $search = null)
    {
        return $this->areaRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $area = $this->areaRepository->getById($id);
        $area->update(['is_active' => !$area->is_active]);
        return $area;
    }
}
