<?php

namespace App\Livewire\Taxes;

use App\Services\TaxService;
use Livewire\Component;
use Livewire\WithPagination;

class TaxIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(int $id, TaxService $taxService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('taxes.edit')) {
            session()->flash('error', 'ليس لديك صلاحية التعديل');
            return;
        }

        $tax = $taxService->toggleActive($id);
        session()->flash('success', $tax->is_active ? 'تم تفعيل الضريبة' : 'تم تعطيل الضريبة');
    }

    public function delete(int $id, TaxService $taxService)
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('taxes.delete')) {
            session()->flash('error', 'ليس لديك صلاحية الحذف');
            return;
        }

        $taxService->deleteTax($id);
        session()->flash('success', 'تم حذف الضريبة بنجاح');
    }

    public function render(TaxService $taxService)
    {
        return view('livewire.taxes.tax-index', [
            'taxes' => $taxService->paginateTaxes(10, $this->search ?: null),
        ]);
    }
}
