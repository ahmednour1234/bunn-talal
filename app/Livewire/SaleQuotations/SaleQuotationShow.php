<?php

namespace App\Livewire\SaleQuotations;

use App\Models\Treasury;
use App\Services\SaleQuotationService;
use App\Services\SaleOrderService;
use Livewire\Component;

class SaleQuotationShow extends Component
{
    public int $quotationId;
    public bool $showConvertForm = false;
    public string $convertPaymentMethod = 'cash';
    public ?int $convertTreasuryId = null;

    public function mount(int $id)
    {
        $this->quotationId = $id;
    }

    public function toggleConvertForm()
    {
        $this->showConvertForm = !$this->showConvertForm;
    }

    public function convertToOrder(SaleQuotationService $service)
    {
        $this->validate([
            'convertPaymentMethod' => 'required|in:cash,credit,partial',
            'convertTreasuryId'    => 'required_if:convertPaymentMethod,cash,partial|nullable|exists:treasuries,id',
        ], [
            'convertTreasuryId.required_if' => 'الخزينة مطلوبة للدفع النقدي',
        ]);

        $admin = auth('admin')->user();

        try {
            $order = $service->convertToOrder($this->quotationId, [
                'admin_id'       => $admin->id,
                'payment_method' => $this->convertPaymentMethod,
                'treasury_id'    => $this->convertTreasuryId,
            ]);
            session()->flash('success', 'تم تحويل عرض السعر إلى طلب مبيعات بنجاح');
            return redirect()->route('sale-orders.show', $order->id);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelQuotation(SaleQuotationService $service)
    {
        try {
            $service->cancelQuotation($this->quotationId);
            session()->flash('success', 'تم رفض عرض السعر');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(SaleQuotationService $service)
    {
        $quotation = $service->getById($this->quotationId);
        $quotation->load(['items.product', 'items.unit', 'customer', 'branch', 'delegate', 'admin', 'order']);

        return view('livewire.sale-quotations.sale-quotation-show', [
            'quotation'  => $quotation,
            'treasuries' => Treasury::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
