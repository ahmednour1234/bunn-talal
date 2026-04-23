<?php

namespace App\Livewire;

use App\Models\PurchaseInvoice;
use App\Models\SaleOrder;
use App\Models\TripBookingRequest;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggleOpen(): void
    {
        $this->open = !$this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        $pendingSaleOrders = SaleOrder::whereIn('status', ['draft', 'confirmed'])->count();

        $lowStockProducts = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->where('products.is_active', true)
            ->select('products.id', 'products.name', DB::raw('SUM(branch_product.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->havingRaw('SUM(branch_product.quantity) <= 5')
            ->orderBy('total_qty')
            ->limit(5)
            ->get();

        $pendingBookingRequests = TripBookingRequest::where('status', 'pending')->count();

        $unpaidPurchases = PurchaseInvoice::whereIn('status', ['confirmed', 'partial_paid'])->count();

        $notifications = collect();

        if ($pendingSaleOrders > 0) {
            $notifications->push([
                'type'  => 'warning',
                'icon'  => 'sale',
                'text'  => "{$pendingSaleOrders} طلب بيع بانتظار التأكيد",
                'route' => route('sale-orders.index'),
            ]);
        }

        if ($pendingBookingRequests > 0) {
            $notifications->push([
                'type'  => 'info',
                'icon'  => 'booking',
                'text'  => "{$pendingBookingRequests} طلب حجز معلق",
                'route' => route('trips.booking-requests'),
            ]);
        }

        if ($unpaidPurchases > 0) {
            $notifications->push([
                'type'  => 'error',
                'icon'  => 'purchase',
                'text'  => "{$unpaidPurchases} فاتورة مشتريات غير مسددة",
                'route' => route('purchase-invoices.index'),
            ]);
        }

        foreach ($lowStockProducts as $product) {
            $notifications->push([
                'type'  => 'stock',
                'icon'  => 'stock',
                'text'  => "مخزون منخفض: {$product->name} ({$product->total_qty})",
                'route' => route('products.index'),
            ]);
        }

        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'count'         => $notifications->count(),
        ]);
    }
}
