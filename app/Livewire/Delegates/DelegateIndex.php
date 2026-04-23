<?php

namespace App\Livewire\Delegates;

use App\Models\Delegate;
use App\Services\DelegateService;
use Livewire\Component;
use Livewire\WithPagination;

class DelegateIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, DelegateService $delegateService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('delegates.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $delegate = $delegateService->toggleActive($id);
        session()->flash('success', $delegate->is_active ? 'تم تفعيل المندوب' : 'تم تعطيل المندوب');
    }

    public function delete(int $id, DelegateService $delegateService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('delegates.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $delegateService->deleteDelegate($id);
        session()->flash('success', 'تم حذف المندوب بنجاح');
    }

    public function render()
    {
        $query = Delegate::query()->with(['branches', 'areas', 'categories']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('livewire.delegates.delegate-index', [
            'delegates' => $query->latest()->paginate(10),
        ]);
    }
}
