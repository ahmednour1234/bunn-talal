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
        // ── Sales stats ─────────────────────────────────────────────
        $saleOrdersTotal  = SaleOrder::whereNotIn('status', ['cancelled'])->sum('total');
        $saleOrdersPaid   = SaleOrder::whereNotIn('status', ['cancelled'])->sum('paid_amount');
        $saleOrdersCount  = SaleOrder::whereNotIn('status', ['cancelled'])->count();
        $saleReturnsTotal = SaleReturn::whereNotIn('status', ['cancelled'])->sum('refund_amount');

        // ── Purchase stats ───────────────────────────────────────────
        $purchaseTotal      = PurchaseInvoice::whereNotIn('status', ['cancelled'])->sum('total');
        $purchasePaid       = PurchaseInvoice::whereNotIn('status', ['cancelled'])->sum('paid_amount');
        $purchaseCount      = PurchaseInvoice::whereNotIn('status', ['cancelled'])->count();
        $purchaseReturnsTotal = PurchaseReturn::whereNotIn('status', ['cancelled'])->sum('refund_amount');

        // ── Treasury / Accounting stats ──────────────────────────────
        $totalTreasuryBalance = Treasury::where('is_active', true)->sum('balance');
        $financialTransactionsCount = FinancialTransaction::count();
        $accountsCount = Account::count();

        // ── Inventory stats ──────────────────────────────────────────
        $productsCount       = Product::where('is_active', true)->count();
        $lowStockCount       = DB::table('branch_product')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('SUM(quantity) <= 5')
            ->get()->count();
        $totalStockValue     = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->sum(DB::raw('branch_product.quantity * products.cost_price'));

        // ── Monthly chart data (last 6 months) ──────────────────────
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $salesByMonth = SaleOrder::whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $purchasesByMonth = PurchaseInvoice::whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartLabels   = $months->map(fn($m) => \Carbon\Carbon::parse($m . '-01')->translatedFormat('M Y'))->values()->toArray();
        $chartSales    = $months->map(fn($m) => round((float)($salesByMonth[$m] ?? 0), 2))->values()->toArray();
        $chartPurchases = $months->map(fn($m) => round((float)($purchasesByMonth[$m] ?? 0), 2))->values()->toArray();

        // ── Recent sale orders ───────────────────────────────────────
        $recentSaleOrders = SaleOrder::with('customer', 'branch')
            ->latest()
            ->take(5)
            ->get();

        // ── Recent purchase invoices ─────────────────────────────────
        $recentPurchaseInvoices = PurchaseInvoice::with('supplier', 'branch')
            ->latest()
            ->take(5)
            ->get();

        // ── Sale orders by status ─────────────────────────────────────
        $saleStatusCounts = SaleOrder::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Delegate performance ─────────────────────────────────────
        $delegatesPerformance = Delegate::where('is_active', true)
            ->orderByDesc('total_collected')
            ->take(6)
            ->get(['id', 'name', 'total_collected', 'total_due', 'cash_custody', 'is_active']);

        $totalDelegatesCustody = Delegate::sum('cash_custody');

        // ── Pending / overdue helpers ────────────────────────────────
        $pendingSaleOrdersCount  = SaleOrder::whereIn('status', ['draft', 'confirmed'])->count();
        $unpaidPurchasesCount    = PurchaseInvoice::whereIn('status', ['confirmed', 'partial_paid'])->count();
        $saleOrdersRemaining     = $saleOrdersTotal - $saleOrdersPaid;
        $purchaseRemaining       = $purchaseTotal - $purchasePaid;
        $confirmedSaleOrdersCount = SaleOrder::where('status', 'confirmed')->count();

        // ── TODAY's sale orders ───────────────────────────────────────
        $todaySaleOrders = SaleOrder::with(['customer', 'delegate'])
            ->whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->take(8)
            ->get();
        $todaySaleOrdersTotal = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');
        $todaySaleOrdersCount = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();
        $todaySaleOrdersPaid = SaleOrder::whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('paid_amount');

        // ── Best-selling products (by qty sold) ──────────────────────
        $topSellingProducts = SaleOrderItem::select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue')
            )
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
            ->groupBy('products.id', 'products.name', 'products.selling_price')
            ->havingRaw('SUM(branch_product.quantity) <= 5')
            ->orderBy('total_qty')
            ->take(8)
            ->get();

        // ── Monthly Revenue vs Expenses (last 6 months) ──────────────
        $revenueByMonth = FinancialTransaction::where('type', 'revenue')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $expenseByMonth = FinancialTransaction::where('type', 'expense')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
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
                DB::raw('SUM(sale_orders.total) as total_sales'),
                DB::raw('SUM(sale_orders.paid_amount) as total_paid'),
                DB::raw('COUNT(sale_orders.id) as orders_count')
            )
            ->join('sale_orders', 'sale_orders.customer_id', '=', 'customers.id')
            ->whereNull('sale_orders.deleted_at')
            ->whereNotIn('sale_orders.status', ['cancelled'])
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('total_sales')
            ->take(8)
            ->get();

        // ── At-risk customers (high outstanding, unpaid orders) ───────
        $atRiskCustomers = Customer::select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.credit_limit',
                DB::raw('SUM(sale_orders.total) as total_sales'),
                DB::raw('SUM(sale_orders.paid_amount) as total_paid'),
                DB::raw('(SUM(sale_orders.total) - SUM(sale_orders.paid_amount)) as outstanding'),
                DB::raw('COUNT(sale_orders.id) as orders_count')
            )
            ->join('sale_orders', 'sale_orders.customer_id', '=', 'customers.id')
            ->whereNull('sale_orders.deleted_at')
            ->whereIn('sale_orders.status', ['confirmed', 'partial_paid'])
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.credit_limit')
            ->havingRaw('(SUM(sale_orders.total) - SUM(sale_orders.paid_amount)) > 0')
            ->orderByDesc('outstanding')
            ->take(8)
            ->get();

        // ── Supplier payment alerts (unpaid invoices, oldest first) ───
        $supplierAlerts = PurchaseInvoice::with('supplier')
            ->whereIn('status', ['confirmed', 'partial_paid'])
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
            ->take(10)
            ->get();

        $overdueSupplierCount = $supplierAlerts->filter(
            fn($inv) => $inv->due_date && $inv->due_date->lt(today())
        )->count();

        // ── Trips alerts ─────────────────────────────────────────────
        $pendingBookingRequests = TripBookingRequest::where('status', 'pending')->count();
        $pendingBookingRequestsList = TripBookingRequest::with(['delegate', 'trip'])
            ->where('status', 'pending')->latest()->take(5)->get();

        $tripDeficitAlerts = Trip::where('status', 'settled')
            ->where(function ($q) {
                $q->where('settlement_cash_deficit', '>', 0)
                  ->orWhere('settlement_product_deficit', '>', 0);
            })
            ->with('delegate')
            ->latest('settled_at')
            ->take(5)
            ->get();

        // ── Trips statistics ─────────────────────────────────────────
        $activeTripsCount     = Trip::whereIn('status', ['active'])->count();
        $draftTripsCount      = Trip::where('status', 'draft')->count();
        $settledTripsCount    = Trip::where('status', 'settled')->count();
        $delegatesOnTrip      = Trip::where('status', 'active')->distinct('delegate_id')->count('delegate_id');
        $tripsWithDeficit     = Trip::where('status', 'settled')
            ->where(fn($q) => $q->where('settlement_cash_deficit', '>', 0)->orWhere('settlement_product_deficit', '>', 0))
            ->count();
        $totalTripCashDeficit    = Trip::where('status', 'settled')->sum('settlement_cash_deficit');
        $totalTripProductDeficit = Trip::where('status', 'settled')->sum('settlement_product_deficit');

        // ── Delegate trip performance ────────────────────────────────
        $delegateTripPerformance = Delegate::select('delegates.*')
            ->selectRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id) as trips_total')
            ->selectRaw('(SELECT COUNT(*) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "active") as trips_active')
            ->selectRaw('(SELECT COALESCE(SUM(settlement_cash_deficit),0) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "settled") as total_cash_def')
            ->selectRaw('(SELECT COALESCE(SUM(settlement_product_deficit),0) FROM trips WHERE trips.delegate_id = delegates.id AND trips.status = "settled") as total_prod_def')
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

        $accountsRevenue = FinancialTransaction::where('type', 'revenue')->sum('amount');
        $accountsExpense = FinancialTransaction::where('type', 'expense')->sum('amount');

        // ── Today's financial transactions ────────────────────────────
        $todayTransactions = FinancialTransaction::with(['account', 'treasury'])
            ->whereDate('date', today())
            ->latest()
            ->take(6)
            ->get();
        $todayTransactionsRevenue = FinancialTransaction::where('type', 'revenue')
            ->whereDate('date', today())->sum('amount');
        $todayTransactionsExpense = FinancialTransaction::where('type', 'expense')
            ->whereDate('date', today())->sum('amount');

        // ── Treasuries list ───────────────────────────────────────────
        $treasuries = Treasury::where('is_active', true)->get(['id', 'name', 'balance']);

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
            'customersCount'             => Customer::count(),
            'delegatesCount'             => Delegate::count(),
            'suppliersCount'             => Supplier::count(),
            'treasuriesCount'            => Treasury::count(),
            // sales
            'saleOrdersTotal'            => $saleOrdersTotal,
            'saleOrdersPaid'             => $saleOrdersPaid,
            'saleOrdersCount'            => $saleOrdersCount,
            'saleReturnsTotal'           => $saleReturnsTotal,
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

