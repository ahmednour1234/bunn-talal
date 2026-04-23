<?php

namespace App\Services;

use App\Repositories\Contracts\DelegateRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DelegateService
{
    public function __construct(protected DelegateRepositoryInterface $delegateRepository)
    {
    }

    public function getAllDelegates()
    {
        return $this->delegateRepository->getAll();
    }

    public function getDelegateById(int $id)
    {
        return $this->delegateRepository->getById($id);
    }

    public function createDelegate(array $data, ?UploadedFile $image = null, array $branchIds = [], array $areaIds = [], array $categoryIds = [])
    {
        if ($image) {
            $data['national_id_image'] = $image->store('delegates/ids', 'public');
        }

        $delegate = $this->delegateRepository->create($data);
        $delegate->branches()->sync($branchIds);
        $delegate->areas()->sync($areaIds);
        $delegate->categories()->sync($categoryIds);

        return $delegate;
    }

    public function updateDelegate(int $id, array $data, ?UploadedFile $image = null, array $branchIds = [], array $areaIds = [], array $categoryIds = [])
    {
        if ($image) {
            $delegate = $this->delegateRepository->getById($id);
            if ($delegate->national_id_image) {
                Storage::disk('public')->delete($delegate->national_id_image);
            }
            $data['national_id_image'] = $image->store('delegates/ids', 'public');
        }

        $delegate = $this->delegateRepository->update($id, $data);
        $delegate->branches()->sync($branchIds);
        $delegate->areas()->sync($areaIds);
        $delegate->categories()->sync($categoryIds);

        return $delegate;
    }

    public function deleteDelegate(int $id): bool
    {
        $delegate = $this->delegateRepository->getById($id);
        if ($delegate->national_id_image) {
            Storage::disk('public')->delete($delegate->national_id_image);
        }

        return $this->delegateRepository->delete($id);
    }

    public function paginateDelegates(int $perPage = 15, ?string $search = null)
    {
        return $this->delegateRepository->paginate($perPage, $search);
    }

    public function toggleActive(int $id)
    {
        $delegate = $this->delegateRepository->getById($id);
        $delegate->update(['is_active' => !$delegate->is_active]);
        return $delegate;
    }
}
