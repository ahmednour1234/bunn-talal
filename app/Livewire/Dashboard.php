<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\Account;
use App\Models\Area;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\FinancialTransaction;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Role;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\Trip;
use App\Models\TripBookingRequest;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // ── Branch filter ────────────────────────────────────────────
        /** @var \App\Models\Admin $admin */
        $admin    = auth('admin')->user();
        $branchId = in_array($admin->type, ['super', 'branches_manager']) ? null : $admin->branch_id;

        $branchFilter = fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q;

        // ── Sales stats ─────────────────────────────────────────────
        $saleOrdersTotal          = SaleOrder::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');
        $saleOrdersPaid           = SaleOrder::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('paid_amount');
        $saleOrdersCount          = SaleOrder::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $saleReturnsTotal         = SaleReturn::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('refund_amount');
        $cancelledSaleOrdersCount = SaleOrder::where('status', 'cancelled')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $cancelledSaleOrdersTotal = SaleOrder::where('status', 'cancelled')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');

        // ── Purchase stats ───────────────────────────────────────────
        $purchaseTotal      = PurchaseInvoice::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total');
        $purchasePaid       = PurchaseInvoice::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('paid_amount');
        $purchaseCount      = PurchaseInvoice::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $purchaseReturnsTotal = PurchaseReturn::whereNotIn('status', ['cancelled'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('refund_amount');

        // ── Treasury / Accounting stats ──────────────────────────────
        $totalTreasuryBalance = Treasury::where('is_active', true)->visibleToBranch($branchId)->sum('balance');
        $financialTransactionsCount = FinancialTransaction::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $accountsCount = Account::count();

        // ── Inventory stats ──────────────────────────────────────────
        $productsCount       = Product::where('is_active', true)->count();
        $lowStockCount       = DB::table('branch_product')
            ->select('product_id')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('product_id')
            ->havingRaw('SUM(quantity) <= 5')
            ->get()->count();
        $totalStockValue     = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->when($branchId, fn($q) => $q->where('branch_product.branch_id', $branchId))
            ->sum(DB::raw('branch_product.quantity * products.cost_price'));

        // ── Monthly chart data (last 6 months) ──────────────────────
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $salesByMonth = SaleOrder::whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $purchasesByMonth = PurchaseInvoice::whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartLabels   = $months->map(fn($m) => \Carbon\Carbon::parse($m . '-01')->translatedFormat('M Y'))->values()->toArray();
        $chartSales    = $months->map(fn($m) => round((float)($salesByMonth[$m] ?? 0), 2))->values()->toArray();
        $chartPurchases = $months->map(fn($m) => round((float)($purchasesByMonth[$m] ?? 0), 2))->values()->toArray();

        // ── Recent sale orders ───────────────────────────────────────
        $recentSaleOrders = SaleOrder::with('customer', 'branch')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(5)
            ->get();

        // ── Recent purchase invoices ─────────────────────────────────
        $recentPurchaseInvoices = PurchaseInvoice::with('supplier', 'branch')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(5)
            ->get();

        // ── Sale orders by status ─────────────────────────────────────
        $saleStatusCounts = SaleOrder::selectRaw('status, COUNT(*) as count')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Delegate performance ─────────────────────────────────────
        $delegatesPerformance = Delegate::where('is_active', true)
            ->when($branchId, fn($q) => $q->whereHas('branches', fn($b) => $b->where('branches.id', $branchId)))
            ->orderByDesc('total_collected')
            ->take(6)
            ->get(['id', 'name', 'total_collected', 'total_due', 'cash_custody', 'is_active']);

        $totalDelegatesCustody = Delegate::when($branchId, fn($q) => $q->whereHas('branches', fn($b) => $b->where('branches.id', $branchId)))->sum('cash_custody');

        // ── Pending / overdue helpers ────────────────────────────────
        $pendingSaleOrdersCount  = SaleOrder::whereIn('status', ['draft', 'confirmed'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $unpaidPurchasesCount    = PurchaseInvoice::whereIn('status', ['confirmed', 'partial_paid'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $saleOrdersRemaining     = $saleOrdersTotal - $saleOrdersPaid;
        $purchaseRemaining       = $purchaseTotal - $purchasePaid;
        $confirmedSaleOrdersCount = SaleOrder::where('status', 'confirmed')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();

        // ── TODAY's sale orders ───────────────────────────────────────
        $todaySaleOrders = SaleOrder::with(['customer', 'delegate'])
            ->whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(8)
            ->get();
        $todaySaleOrdersTotal = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total');
        $todaySaleOrdersCount = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();
        $todaySaleOrdersPaid = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('paid_amount');

        // ── Best-selling products (by qty sold) ──────────────────────
        $topSellingProducts = SaleOrderItem::select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->whereHas('order', fn($q) => $q->whereNotIn('status', ['cancelled', 'draft'])->when($branchId, fn($q2) => $q2->where('branch_id', $branchId)))
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(8)
            ->get();

        // ── Low-stock products (with details) ────────────────────────
        $lowStockProducts = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->select(
                'products.id',
                'products.name',
                'products.selling_price',
                DB::raw('SUM(branch_product.quantity) as total_qty')
            )
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->when($branchId, fn($q) => $q->where('branch_product.branch_id', $branchId))
            ->groupBy('products.id', 'products.name', 'products.selling_price')
            ->havingRaw('SUM(branch_product.quantity) <= 5')
            ->orderBy('total_qty')
            ->take(8)
            ->get();

        // ── Monthly Revenue vs Expenses (last 6 months) ──────────────
        $revenueByMonth = FinancialTransaction::where('type', 'revenue')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $expenseByMonth = FinancialTransaction::where('type', 'expense')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartRevenue = $months->map(fn($m) => round((float)($revenueByMonth[$m] ?? 0), 2))->values()->toArray();
        $chartExpense = $months->map(fn($m) => round((float)($expenseByMonth[$m] ?? 0), 2))->values()->toArray();

        // ── Top customers (by total sales value) ─────────────────────
        $topCustomers = Customer::select(
                'customers.id',
                'customers.name',
                'customers.phone',
                DB::raw('SUM(CASE WHEN sale_orders.status NOT IN ("cancelled","draft") THEN sale_orders.total ELSE 0 END) as total_sales'),
                DB::raw('SUM(CASE WHEN sale_orders.status NOT IN ("cancelled","draft") THEN sale_orders.paid_amount ELSE 0 END) as total_paid'),
                DB::raw('SUM(CASE WHEN sale_orders.status NOT IN ("cancelled","draft") THEN 1 ELSE 0 END) as orders_count'),
                DB::raw('SUM(CASE WHEN sale_orders.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count')
            )
            ->join('sale_orders', 'sale_orders.customer_id', '=', 'customers.id')
            ->whereNull('sale_orders.deleted_at')
            ->when($branchId, fn($q) => $q->where('customers.branch_id', $branchId))
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->havingRaw('SUM(CASE WHEN sale_orders.status NOT IN ("cancelled","draft") THEN sale_orders.total ELSE 0 END) > 0')
            ->orderByDesc('total_sales')
            ->take(8)
            ->get();

        // ── At-risk customers (high outstanding, unpaid orders) ───────
        // Returns subquery reused in multiple expressions
        $returnsSubquery = '(SELECT COALESCE(SUM(sr.refund_amount), 0) FROM sale_returns sr WHERE sr.customer_id = customers.id AND sr.status IN ("confirmed","refunded") AND sr.deleted_at IS NULL)';

        $atRiskCustomers = Customer::select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.credit_limit',
                'customers.opening_balance',
                DB::raw('SUM(sale_orders.total) as total_sales'),
                DB::raw('SUM(sale_orders.paid_amount) as total_paid'),
                DB::raw("{$returnsSubquery} as total_returns"),
                DB::raw("(customers.opening_balance + SUM(sale_orders.total) - SUM(sale_orders.paid_amount) - {$returnsSubquery}) as outstanding"),
                DB::raw('COUNT(sale_orders.id) as orders_count'),
                DB::raw('(SELECT COUNT(*) FROM sale_orders s2 WHERE s2.customer_id = customers.id AND s2.status = "cancelled" AND s2.deleted_at IS NULL) as cancelled_count')
            )
            ->join('sale_orders', 'sale_orders.customer_id', '=', 'customers.id')
            ->whereNull('sale_orders.deleted_at')
            ->whereIn('sale_orders.status', ['confirmed', 'partial_paid'])
            ->when($branchId, fn($q) => $q->where('customers.branch_id', $branchId))
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.credit_limit', 'customers.opening_balance')
            ->havingRaw("(customers.opening_balance + SUM(sale_orders.total) - SUM(sale_orders.paid_amount) - {$returnsSubquery}) > 0")
            ->orderByRaw("(customers.opening_balance + SUM(sale_orders.total) - SUM(sale_orders.paid_amount) - {$returnsSubquery}) DESC")
            ->take(8)
            ->get();

        // ── Supplier payment alerts (unpaid invoices, oldest first) ───
        $supplierAlerts = PurchaseInvoice::with('supplier')
            ->whereIn('status', ['confirmed', 'partial_paid'])
            ->whereNull('deleted_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
            ->take(10)
            ->get();

        $overdueSupplierCount = $supplierAlerts->filter(
            fn($inv) => $inv->due_date && $inv->due_date->lt(today())
        )->count();

        // ── Trips alerts ─────────────────────────────────────────────
        $pendingBookingRequests = TripBookingRequest::where('status', 'pending')
            ->when($branchId, fn($q) => $q->whereHas('delegate.branches', fn($b) => $b->where('branches.id', $branchId)))
            ->count();
        $pendingBookingRequestsList = TripBookingRequest::with(['delegate', 'trip'])
            ->when($branchId, fn($q) => $q->whereHas('delegate.branches', fn($b) => $b->where('branches.id', $branchId)))
            ->where('status', 'pending')->latest()->take(5)->get();

        $tripDeficitAlerts = Trip::where('status', 'settled')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(function ($q) {
                $q->where('settlement_cash_deficit', '>', 0)
                  ->orWhere('settlement_product_deficit', '>', 0);
            })
            ->with('delegate')
            ->latest('settled_at')
            ->take(5)
            ->get();

        // ── Trips statistics ─────────────────────────────────────────
        $activeTripsCount     = Trip::whereIn('status', ['active'])->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $draftTripsCount      = Trip::where('status', 'draft')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $settledTripsCount    = Trip::where('status', 'settled')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $delegatesOnTrip      = Trip::where('status', 'active')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->distinct('delegate_id')->count('delegate_id');
        $tripsWithDeficit     = Trip::where('status', 'settled')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(fn($q) => $q->where('settlement_cash_deficit', '>', 0)->orWhere('settlement_product_deficit', '>', 0))
            ->count();
        $totalTripCashDeficit    = Trip::where('status', 'settled')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('settlement_cash_deficit');
        $totalTripProductDeficit = Trip::where('status', 'settled')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('settlement_product_deficit');

        // ── Delegate trip performance ────────────────────────────────
        $delegateTripPerformance = Delegate::select('delegates.*')
            ->selectRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id) as trips_total')
            ->selectRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "active") as trips_active')
            ->selectRaw('(SELECT COALESCE(SUM(settlement_cash_deficit),0) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "settled") as total_cash_def')
            ->selectRaw('(SELECT COALESCE(SUM(settlement_product_deficit),0) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "settled") as total_prod_def')
            ->when($branchId, fn($q) => $q->whereHas('branches', fn($b) => $b->where('branches.id', $branchId)))
            ->where('is_active', true)
            ->whereRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id) > 0')
            ->orderByRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id) DESC')
            ->take(10)
            ->get();

        // ── Accounts with balances ────────────────────────────────────
        $accounts = Account::where('is_active', true)
            ->withCount('financialTransactions')
            ->orderBy('name')
            ->take(10)
            ->get();

        $accountsRevenue = FinancialTransaction::where('type', 'revenue')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('amount');
        $accountsExpense = FinancialTransaction::where('type', 'expense')->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('amount');

        // ── Today's financial transactions ────────────────────────────
        $todayTransactions = FinancialTransaction::with(['account', 'treasury'])
            ->whereDate('date', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(6)
            ->get();
        $todayTransactionsRevenue = FinancialTransaction::where('type', 'revenue')
            ->whereDate('date', today())->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('amount');
        $todayTransactionsExpense = FinancialTransaction::where('type', 'expense')
            ->whereDate('date', today())->when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('amount');

        // ── Treasuries list ───────────────────────────────────────────
        $treasuries = Treasury::where('is_active', true)->visibleToBranch($branchId)->get(['id', 'name', 'balance']);

        return view('livewire.dashboard', [
            // legacy counts
            'branchesCount'              => Branch::count(),
            'vehiclesCount'              => Vehicle::count(),
            'categoriesCount'            => Category::count(),
            'unitsCount'                 => Unit::count(),
            'adminsCount'                => Admin::count(),
            'rolesCount'                 => Role::count(),
            'permissionsCount'           => Permission::count(),
            'areasCount'                 => Area::count(),
            'customersCount'             => Customer::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'delegatesCount'             => Delegate::when($branchId, fn($q) => $q->whereHas('branches', fn($b) => $b->where('branches.id', $branchId)))->count(),
            'suppliersCount'             => Supplier::count(),
            'treasuriesCount'            => Treasury::count(),
            // sales
            'saleOrdersTotal'            => $saleOrdersTotal,
            'saleOrdersPaid'             => $saleOrdersPaid,
            'saleOrdersCount'            => $saleOrdersCount,
            'saleReturnsTotal'           => $saleReturnsTotal,
            'cancelledSaleOrdersCount'   => $cancelledSaleOrdersCount,
            'cancelledSaleOrdersTotal'   => $cancelledSaleOrdersTotal,
            // purchases
            'purchaseTotal'              => $purchaseTotal,
            'purchasePaid'               => $purchasePaid,
            'purchaseCount'              => $purchaseCount,
            'purchaseReturnsTotal'       => $purchaseReturnsTotal,
            // treasury
            'totalTreasuryBalance'       => $totalTreasuryBalance,
            'financialTransactionsCount' => $financialTransactionsCount,
            'accountsCount'              => $accountsCount,
            // inventory
            'productsCount'              => $productsCount,
            'lowStockCount'              => $lowStockCount,
            'totalStockValue'            => $totalStockValue,
            // charts
            'chartLabels'                => $chartLabels,
            'chartSales'                 => $chartSales,
            'chartPurchases'             => $chartPurchases,
            // recent
            'recentSaleOrders'           => $recentSaleOrders,
            'recentPurchaseInvoices'     => $recentPurchaseInvoices,
            'saleStatusCounts'           => $saleStatusCounts,
            // delegates performance
            'delegatesPerformance'       => $delegatesPerformance,
            'totalDelegatesCustody'      => $totalDelegatesCustody,
            // extra helpers
            'pendingSaleOrdersCount'     => $pendingSaleOrdersCount,
            'unpaidPurchasesCount'       => $unpaidPurchasesCount,
            'saleOrdersRemaining'        => $saleOrdersRemaining,
            'purchaseRemaining'          => $purchaseRemaining,
            'confirmedSaleOrdersCount'   => $confirmedSaleOrdersCount,
            // today's orders
            'todaySaleOrders'            => $todaySaleOrders,
            'todaySaleOrdersTotal'       => $todaySaleOrdersTotal,
            'todaySaleOrdersCount'       => $todaySaleOrdersCount,
            'todaySaleOrdersPaid'        => $todaySaleOrdersPaid,
            // best sellers
            'topSellingProducts'         => $topSellingProducts,
            // low stock
            'lowStockProducts'           => $lowStockProducts,
            // accounts
            'accounts'                   => $accounts,
            'accountsRevenue'            => $accountsRevenue,
            'accountsExpense'            => $accountsExpense,
            // today's transactions
            'todayTransactions'          => $todayTransactions,
            'todayTransactionsRevenue'   => $todayTransactionsRevenue,
            'todayTransactionsExpense'   => $todayTransactionsExpense,
            // treasuries
            'treasuries'                 => $treasuries,
            // revenue/expense chart
            'chartRevenue'               => $chartRevenue,
            'chartExpense'               => $chartExpense,
            // top customers
            'topCustomers'               => $topCustomers,
            // at-risk customers
            'atRiskCustomers'            => $atRiskCustomers,
            // supplier alerts
            'supplierAlerts'             => $supplierAlerts,
            'overdueSupplierCount'       => $overdueSupplierCount,
            // trips alerts
            'pendingBookingRequests'       => $pendingBookingRequests,
            'pendingBookingRequestsList'   => $pendingBookingRequestsList,
            'tripDeficitAlerts'            => $tripDeficitAlerts,
            // trips statistics
            'activeTripsCount'             => $activeTripsCount,
            'draftTripsCount'              => $draftTripsCount,
            'settledTripsCount'            => $settledTripsCount,
            'delegatesOnTrip'              => $delegatesOnTrip,
            'tripsWithDeficit'             => $tripsWithDeficit,
            'totalTripCashDeficit'         => $totalTripCashDeficit,
            'totalTripProductDeficit'      => $totalTripProductDeficit,
            'delegateTripPerformance'      => $delegateTripPerformance,
        ]);
    }
}

