<?php

namespace App\Livewire\Installments;

use App\Models\Treasury;
use App\Services\InstallmentService;
use Livewire\Component;

class InstallmentShow extends Component
{
    public int $planId;

    // Payment form
    public string $payEntryId     = '';
    public string $payAmount      = '';
    public string $payTreasuryId  = '';
    public string $payNotes       = '';
    public bool   $showPayForm    = false;

    public function mount(int $id): void
    {
        $this->planId = $id;
    }

    public function openPayForm(int $entryId, float $remaining): void
    {
        $this->payEntryId    = $entryId;
        $this->payAmount     = $remaining;
        $this->payTreasuryId = '';
        $this->payNotes      = '';
        $this->showPayForm   = true;
    }

    public function closePayForm(): void
    {
        $this->showPayForm = false;
        $this->reset(['payEntryId', 'payAmount', 'payTreasuryId', 'payNotes']);
    }

    public function payInstallment(InstallmentService $service): void
    {
        $this->validate([
            'payAmount'     => 'required|numeric|min:0.01',
            'payTreasuryId' => 'required|exists:treasuries,id',
        ], [
            'payAmount.required'     => 'المبلغ مطلوب',
            'payTreasuryId.required' => 'الخزينة مطلوبة',
        ]);

        $admin = auth('admin')->user();

        try {
            $service->payEntry(
                (int) $this->payEntryId,
                (float) $this->payAmount,
                (int) $this->payTreasuryId,
                $admin->id,
                $this->payNotes ?: null,
            );
            $this->closePayForm();
            session()->flash('success', 'تم تسجيل الدفعة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelPlan(InstallmentService $service): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('installments.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        try {
            $service->cancelPlan($this->planId);
            session()->flash('success', 'تم إلغاء الخطة');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(InstallmentService $service): mixed
    {
        // Auto-mark overdue entries on each page load
        $plan = $service->getById($this->planId);
        $service->markOverdue($plan);

        $plan->load(['entries.treasury', 'entries.admin', 'customer', 'supplier', 'branch', 'admin', 'treasury']);

        return view('livewire.installments.installment-show', [
            'plan'       => $plan,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
