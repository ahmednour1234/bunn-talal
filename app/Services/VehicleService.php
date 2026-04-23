<?php

namespace App\Services;

use App\Repositories\Contracts\VehicleRepositoryInterface;

class VehicleService
{
    public function __construct(protected VehicleRepositoryInterface $vehicleRepository)
    {
    }

    public function getAllVehicles()
    {
        return $this->vehicleRepository->getAll();
    }

    public function getVehicleById(int $id)
    {
        return $this->vehicleRepository->getById($id);
    }

    public function createVehicle(array $data)
    {
        return $this->vehicleRepository->create($data);
    }

    public function updateVehicle(int $id, array $data)
    {
        return $this->vehicleRepository->update($id, $data);
    }

    public function deleteVehicle(int $id): bool
    {
        return $this->vehicleRepository->delete($id);
    }

    public function paginateVehicles(int $perPage = 15, ?string $search = null)
    {
        return $this->vehicleRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $vehicle = $this->vehicleRepository->getById($id);
        $vehicle->update(['is_active' => !$vehicle->is_active]);
        return $vehicle;
    }
}
