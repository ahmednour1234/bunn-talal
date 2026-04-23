<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use Livewire\Component;

class CollectionShow extends Component
{
    public int $collectionId;

    public function mount(int $id): void
    {
        $this->collectionId = $id;
    }

    public function cancelCollection(): void
    {
        $admin = auth('admin')->user();
        if (!$admin->hasPermission('collections.edit')) {
            session()->flash('error', 'ليس لديك صلاحية');
            return;
        }
        $col = Collection::findOrFail($this->collectionId);
        if ($col->status === 'cancelled') {
            session()->flash('error', 'التحصيل ملغي بالفعل');
            return;
        }

        // Reverse the paid_amount on orders
        foreach ($col->items as $item) {
            $order = $item->saleOrder;
            if ($order) {
                $newPaid = max(0, round((float) $order->paid_amount - (float) $item->amount, 2));
                $newStatus = $newPaid <= 0 ? 'confirmed' : ($newPaid >= (float) $order->total ? 'paid' : 'partial_paid');
                $order->update(['paid_amount' => $newPaid, 'status' => $newStatus]);
            }
        }

        $col->update(['status' => 'cancelled']);
        session()->flash('success', 'تم إلغاء التحصيل واسترداد المبالغ من الطلبات');
    }

    public function render()
    {
        $collection = Collection::with([
            'delegate', 'customer', 'branch', 'treasury', 'admin',
            'items.saleOrder.customer',
        ])->findOrFail($this->collectionId);

        return view('livewire.collections.collection-show', compact('collection'));
    }
}
