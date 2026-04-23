<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
use App\Models\InventoryDispatch;
use App\Models\ProductDepreciation;
use Illuminate\Support\Facades\DB;

class BranchReportService
{
    public function getBranchInventoryReport(?int $branchId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $query = DB::table('branch_product')
            ->join('products', 'branch_product.product_id', '=', 'products.id')
            ->join('branches', 'branch_product.branch_id', '=', 'branches.id')
            ->leftJoin('units', 'branch_product.unit_id', '=', 'units.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'branches.id as branch_id',
                'branches.name as branch_name',
                'products.id as product_id',
                'products.name as product_name',
                'categories.name as category_name',
                'branch_product.quantity',
                'units.name as unit_name',
                'units.symbol as unit_symbol',
                'products.cost_price',
                'products.selling_price',
                DB::raw('branch_product.quantity * products.cost_price as cost_value'),
                DB::raw('branch_product.quantity * products.selling_price as selling_value')
            )
            ->where('branch_product.quantity', '>', 0);

        if ($branchId) {
            $query->where('branch_product.branch_id', $branchId);
        }

        if ($dateFrom) {
            $query->whereDate('branch_product.updated_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('branch_product.updated_at', '<=', $dateTo);
        }

        return $query->orderBy('branches.name')->orderBy('products.name')->get();
    }

    public function getAllBranchesSummary(?string $dateFrom = null, ?string $dateTo = null)
    {
        $query = DB::table('branch_product')
            ->join('branches', 'branch_product.branch_id', '=', 'branches.id')
            ->join('products', 'branch_product.product_id', '=', 'products.id')
            ->where('branch_product.quantity', '>', 0)
            ->where('branches.is_active', true)
            ->groupBy('branches.id', 'branches.name')
            ->select(
                'branches.id',
                'branches.name as branch_name',
                DB::raw('COUNT(DISTINCT branch_product.product_id) as total_products'),
                DB::raw('SUM(branch_product.quantity) as total_quantity'),
                DB::raw('SUM(branch_product.quantity * products.cost_price) as total_cost_value'),
                DB::raw('SUM(branch_product.quantity * products.selling_price) as total_selling_value')
            )
            ->orderBy('branches.name');

        if ($dateFrom) {
            $query->whereDate('branch_product.updated_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('branch_product.updated_at', '<=', $dateTo);
        }

        return $query->get();
    }

    public function getBranchMovementReport(int $branchId, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $branch = Branch::findOrFail($branchId);

        // Purchases IN
        $purchasesQuery = PurchaseInvoice::where('branch_id', $branchId)
            ->whereNotIn('status', ['cancelled', 'draft']);
        if ($dateFrom) $purchasesQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $purchasesQuery->whereDate('date', '<=', $dateTo);
        $purchases = $purchasesQuery->with('items.product')->get();

        // Purchase Returns OUT
        $returnsQuery = PurchaseReturn::where('branch_id', $branchId)
            ->whereNotIn('status', ['cancelled']);
        if ($dateFrom) $returnsQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $returnsQuery->whereDate('date', '<=', $dateTo);
        $returns = $returnsQuery->with('items.product')->get();

        // Transfers IN
        $transfersInQuery = StockTransfer::where('to_branch_id', $branchId)
            ->where('status', 'received');
        if ($dateFrom) $transfersInQuery->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $transfersInQuery->whereDate('created_at', '<=', $dateTo);
        $transfersIn = $transfersInQuery->with('items.product')->get();

        // Transfers OUT
        $transfersOutQuery = StockTransfer::where('from_branch_id', $branchId)
            ->whereIn('status', ['approved', 'received']);
        if ($dateFrom) $transfersOutQuery->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $transfersOutQuery->whereDate('created_at', '<=', $dateTo);
        $transfersOut = $transfersOutQuery->with('items.product')->get();

        // Dispatches OUT
        $dispatchesQuery = InventoryDispatch::where('branch_id', $branchId)
            ->whereNotIn('status', ['pending']);
        if ($dateFrom) $dispatchesQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $dispatchesQuery->whereDate('date', '<=', $dateTo);
        $dispatches = $dispatchesQuery->with('items.product')->get();

        // Depreciations
        $depreciationsQuery = ProductDepreciation::where('branch_id', $branchId)
            ->where('status', 'approved');
        if ($dateFrom) $depreciationsQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $depreciationsQuery->whereDate('date', '<=', $dateTo);
        $depreciations = $depreciationsQuery->with('items.product')->get();

        return [
            'branch' => $branch,
            'purchases' => $purchases,
            'returns' => $returns,
            'transfers_in' => $transfersIn,
            'transfers_out' => $transfersOut,
            'dispatches' => $dispatches,
            'depreciations' => $depreciations,
            'summary' => [
                'total_purchases' => $purchases->sum('total'),
                'total_returns' => $returns->sum('subtotal'),
                'total_losses' => $returns->sum('loss_amount') + $depreciations->sum('total_loss'),
                'total_dispatches' => $dispatches->sum('total_cost'),
                'total_depreciation' => $depreciations->sum('total_loss'),
            ],
        ];
    }
}
