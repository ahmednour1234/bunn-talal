<?php

namespace App\Livewire\Installments;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InstallmentPlan;
use App\Models\PurchaseInvoice;
use App\Models\SaleOrder;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Services\InstallmentService;
use Livewire\Component;

class InstallmentForm extends Component
{
    // Party
    public string $partyType = 'customer';
    public string $customerId = '';
    public string $supplierId = '';

    // Reference (optional)
    public string $referenceType = 'manual';
    public string $referenceId   = '';

    // Plan details
    public string $branchId          = '';
    public string $treasuryId        = '';
    public string $startDate         = '';
    public string $totalAmount       = '';
    public string $downPayment       = '0';
    public string $installmentsCount = '6';
    public string $frequency         = 'monthly';
    public string $notes             = '';

    // Computed preview
    public float $previewInstallmentAmount = 0;
    public float $previewRemaining         = 0;

    // Reference options loaded dynamically
    public array $referenceOptions = [];

    public function mount(): void
    {
        $this->startDate = now()->toDateString();
    }

    public function updatedPartyType(): void
    {
        $this->referenceType = 'manual';
        $this->referenceId   = '';
        $this->customerId    = '';
        $this->supplierId    = '';
        $this->referenceOptions = [];
    }

    public function updatedReferenceType(): void
    {
        $this->referenceId = '';
        $this->referenceOptions = [];
        $this->loadReferenceOptions();
    }

    public function updatedCustomerId(): void
    {
        $this->referenceId = '';
        $this->loadReferenceOptions();
    }

    public function updatedSupplierId(): void
    {
        $this->referenceId = '';
        $this->loadReferenceOptions();
    }

    protected function loadReferenceOptions(): void
    {
        if ($this->referenceType === 'manual') {
            $this->referenceOptions = [];
            return;
        }

        if ($this->referenceType === 'sale_order' && $this->customerId) {
            $this->referenceOptions = SaleOrder::where('customer_id', (int) $this->customerId)
                ->whereIn('status', ['confirmed', 'partial_paid'])
                ->orderByDesc('date')
                ->get(['id', 'order_number', 'total', 'paid_amount'])
                ->map(fn($o) => [
                    'id'    => $o->id,
                    'label' => $o->order_number . ' — المتبقي: ' . number_format($o->total - $o->paid_amount, 2),
                    'total' => (float) $o->total - (float) $o->paid_amount,
                ])->toArray();
        }

        if ($this->referenceType === 'purchase_invoice' && $this->supplierId) {
            $this->referenceOptions = PurchaseInvoice::where('supplier_id', (int) $this->supplierId)
                ->whereIn('status', ['confirmed', 'partial_paid'])
                ->orderByDesc('date')
                ->get(['id', 'invoice_number', 'total', 'paid_amount'])
                ->map(fn($inv) => [
                    'id'    => $inv->id,
                    'label' => $inv->invoice_number . ' — المتبقي: ' . number_format($inv->total - $inv->paid_amount, 2),
                    'total' => (float) $inv->total - (float) $inv->paid_amount,
                ])->toArray();
        }
    }

    public function updatedReferenceId(): void
    {
        // Auto-fill totalAmount from selected reference
        if ($this->referenceId && !empty($this->referenceOptions)) {
            $selected = collect($this->referenceOptions)->firstWhere('id', (int) $this->referenceId);
            if ($selected) {
                $this->totalAmount = $selected['total'];
                $this->recalcPreview();
            }
        }
    }

    public function updatedTotalAmount(): void { $this->recalcPreview(); }
    public function updatedDownPayment(): void { $this->recalcPreview(); }
    public function updatedInstallmentsCount(): void { $this->recalcPreview(); }

    protected function recalcPreview(): void
    {
        $total    = (float) $this->totalAmount;
        $down     = (float) $this->downPayment;
        $count    = max(1, (int) $this->installmentsCount);
        $remaining = max(0, $total - $down);
        $this->previewRemaining         = $remaining;
        $this->previewInstallmentAmount = $count > 0 ? round($remaining / $count, 2) : 0;
    }

    public function save(InstallmentService $service)
    {
        $this->validate([
            'partyType'          => 'required|in:customer,supplier',
            'customerId'         => 'required_if:partyType,customer',
            'supplierId'         => 'required_if:partyType,supplier',
            'branchId'           => 'required|exists:branches,id',
            'startDate'          => 'required|date',
            'totalAmount'        => 'required|numeric|min:0.01',
            'downPayment'        => 'nullable|numeric|min:0',
            'installmentsCount'  => 'required|integer|min:1|max:120',
            'frequency'          => 'required|in:weekly,biweekly,monthly,custom',
        ], [
            'customerId.required_if'   => 'يجب اختيار العميل',
            'supplierId.required_if'   => 'يجب اختيار المورد',
            'branchId.required'        => 'الفرع مطلوب',
            'totalAmount.required'     => 'المبلغ الإجمالي مطلوب',
            'installmentsCount.min'    => 'عدد الأقساط يجب أن يكون 1 على الأقل',
        ]);

        $admin = auth('admin')->user();

        try {
            $plan = $service->createPlan([
                'party_type'         => $this->partyType,
                'customer_id'        => $this->partyType === 'customer' ? (int) $this->customerId : null,
                'supplier_id'        => $this->partyType === 'supplier' ? (int) $this->supplierId : null,
                'reference_type'     => $this->referenceType,
                'reference_id'       => $this->referenceId ?: null,
                'branch_id'          => (int) $this->branchId,
                'admin_id'           => $admin->id,
                'treasury_id'        => $this->treasuryId ?: null,
                'start_date'         => $this->startDate,
                'total_amount'       => (float) $this->totalAmount,
                'down_payment'       => (float) $this->downPayment,
                'installments_count' => (int) $this->installmentsCount,
                'frequency'          => $this->frequency,
                'notes'              => $this->notes ?: null,
            ]);

            session()->flash('success', 'تم إنشاء خطة التقسيط بنجاح - ' . $plan->plan_number);
            $this->redirect(route('installments.show', $plan->id));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.installments.installment-form', [
            'customers'      => Customer::where('is_active', true)->orderBy('name')->get(),
            'suppliers'      => Supplier::where('is_active', true)->orderBy('name')->get(),
            'branches'       => Branch::where('is_active', true)->orderBy('name')->get(),
            'treasuries'     => Treasury::where('is_active', true)->orderBy('name')->get(),
            'partyLabels'    => InstallmentPlan::partyTypeLabels(),
            'frequencyLabels'=> InstallmentPlan::frequencyLabels(),
        ]);
    }
}
