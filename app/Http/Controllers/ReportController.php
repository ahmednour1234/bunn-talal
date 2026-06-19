<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Delegate;
use App\Models\FinancialTransaction;
use App\Models\InstallmentPlan;
use App\Models\Product;
use App\Models\ProductDepreciation;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Treasury;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.reports.index');
    }

    public function incomeStatement()
    {
        return view('pages.reports.income-statement');
    }

    public function accountStatement()
    {
        return view('pages.reports.account-statement');
    }

    public function balanceSheet()
    {
        return view('pages.reports.balance-sheet');
    }

    public function treasuryStatement()
    {
        return view('pages.reports.treasury-statement');
    }

    public function salesCollections(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   now()->format('Y-m-d'));

        // ══════════════════════════════════════════════════════════
        // SALES — الفواتير (المبيعات)
        // ══════════════════════════════════════════════════════════
        $ordersInPeriod = SaleOrder::whereBetween('date', [$dateFrom, $dateTo]);

        // الفواتير النشطة (غير المسودة وغير الملغية)
        $activeOrders = (clone $ordersInPeriod)->whereNotIn('status', ['draft', 'cancelled']);

        $salesTotal     = (float) (clone $activeOrders)->sum('total');      // بعنا بكام
        $salesCollected = (float) (clone $activeOrders)->sum('paid_amount'); // حصلنا كام من الفواتير
        $salesCount     = (clone $activeOrders)->count();
        $salesOutstanding = max(0, $salesTotal - $salesCollected);          // المتبقي (آجل)

        // الفواتير الملغية
        $cancelledOrders     = (clone $ordersInPeriod)->where('status', 'cancelled');
        $cancelledOrdersTotal = (float) (clone $cancelledOrders)->sum('total');
        $cancelledOrdersCount = (clone $cancelledOrders)->count();

        // ══════════════════════════════════════════════════════════
        // COLLECTIONS — سندات التحصيل (من المناديب/العملاء)
        // ══════════════════════════════════════════════════════════
        $collectionsInPeriod = Collection::whereBetween('collection_date', [$dateFrom, $dateTo]);

        $collectionsTotal = (float) (clone $collectionsInPeriod)->where('status', 'completed')->sum('total_amount');
        $collectionsCount = (clone $collectionsInPeriod)->where('status', 'completed')->count();

        $cancelledCollectionsTotal = (float) (clone $collectionsInPeriod)->where('status', 'cancelled')->sum('total_amount');
        $cancelledCollectionsCount = (clone $collectionsInPeriod)->where('status', 'cancelled')->count();

        // إجمالي المحصّل = مدفوعات الفواتير وقت البيع + سندات التحصيل المكتملة
        $totalCollected = $salesCollected + $collectionsTotal;

        // ══════════════════════════════════════════════════════════
        // RETURNS — المرتجعات
        // ══════════════════════════════════════════════════════════
        $returnsInPeriod = SaleReturn::whereBetween('date', [$dateFrom, $dateTo]);

        // المرتجعات المؤكدة/المستردة
        $activeReturns      = (clone $returnsInPeriod)->whereIn('status', ['confirmed', 'refunded']);
        $returnsTotal       = (float) (clone $activeReturns)->sum('refund_amount'); // مرتجعات بكام
        $returnsCount       = (clone $activeReturns)->count();

        // المرتجعات الملغية
        $cancelledReturns      = (clone $returnsInPeriod)->where('status', 'cancelled');
        $cancelledReturnsTotal = (float) (clone $cancelledReturns)->sum('refund_amount');
        $cancelledReturnsCount = (clone $cancelledReturns)->count();

        // ══════════════════════════════════════════════════════════
        // NET — الصافي
        // ══════════════════════════════════════════════════════════
        $netSales      = $salesTotal - $returnsTotal;       // صافي المبيعات (بعد خصم المرتجعات)
        $netCollected  = $totalCollected - $returnsTotal;   // صافي المحصّل (بعد رد المرتجعات)

        return view('pages.reports.sales-collections', compact(
            'dateFrom', 'dateTo',
            // Sales
            'salesTotal', 'salesCollected', 'salesCount', 'salesOutstanding',
            'cancelledOrdersTotal', 'cancelledOrdersCount',
            // Collections
            'collectionsTotal', 'collectionsCount',
            'cancelledCollectionsTotal', 'cancelledCollectionsCount',
            'totalCollected',
            // Returns
            'returnsTotal', 'returnsCount',
            'cancelledReturnsTotal', 'cancelledReturnsCount',
            // Net
            'netSales', 'netCollected',
        ));
    }

    /**
     * تقرير أرباح المنتجات — لكل منتج خلال فترة وفرع:
     * سعر الشراء/البيع، الكمية المباعة، المبيعات، التكلفة، الربح،
     * المرتجعات، المتبقي في المخزون، وعدد الفواتير. مع فلتر الفرع والتاريخ.
     */
    public function productProfit(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   now()->format('Y-m-d'));

        // المستخدم المقيّد بفرع يُجبر على فرعه
        $scopedBranchId = auth('admin')->user()?->scopedBranchId();
        $branchId = $scopedBranchId ?: $request->input('branch_id', '');

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // ── الفواتير النشطة في الفترة (والفرع) ──────────────────────
        $activeOrdersQuery = SaleOrder::whereBetween('date', [$dateFrom, $dateTo])
            ->whereNotIn('status', ['draft', 'cancelled']);
        if ($branchId) {
            $activeOrdersQuery->where('branch_id', $branchId);
        }
        $activeOrderIds = (clone $activeOrdersQuery)->pluck('id');

        // ── ملخص الفواتير (موجودة / ملغية) ─────────────────────────
        $ordersBase = SaleOrder::whereBetween('date', [$dateFrom, $dateTo]);
        if ($branchId) {
            $ordersBase->where('branch_id', $branchId);
        }
        $activeOrdersCount    = (clone $ordersBase)->whereNotIn('status', ['draft', 'cancelled'])->count();
        $cancelledOrdersCount = (clone $ordersBase)->where('status', 'cancelled')->count();

        // ── بنود البيع مجمّعة حسب المنتج ───────────────────────────
        // cost_total = تكلفة البنود التي لها سعر تكلفة مسجّل وقت البيع.
        // qty_missing_cost = كمية البنود التي سعر تكلفتها 0 (فواتير قديمة) —
        // تُحسب تكلفتها لاحقاً من تكلفة الفرع/المنتج الحالية حتى لا تكون التكلفة صفراً.
        $soldRows = \App\Models\SaleOrderItem::whereIn('sale_order_id', $activeOrderIds)
            ->selectRaw('product_id,
                SUM(quantity)                                            as qty_sold,
                SUM(total)                                               as sales_total,
                SUM(cost_price * quantity)                               as cost_total,
                SUM(CASE WHEN cost_price > 0 THEN 0 ELSE quantity END)   as qty_missing_cost,
                COUNT(DISTINCT sale_order_id)                            as invoices_count')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // ── المرتجعات النشطة في الفترة (والفرع) ─────────────────────
        $returnsQuery = SaleReturn::whereBetween('date', [$dateFrom, $dateTo])
            ->whereIn('status', ['confirmed', 'refunded']);
        if ($branchId) {
            $returnsQuery->where('branch_id', $branchId);
        }
        $returnIds = (clone $returnsQuery)->pluck('id');
        $returnsCount = $returnIds->count();

        $returnedRows = \App\Models\SaleReturnItem::whereIn('sale_return_id', $returnIds)
            ->selectRaw('product_id,
                SUM(quantity)        as qty_returned,
                SUM(refund_amount)   as refund_total')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // ── المنتجات التي ظهرت في البيع أو المرتجع ──────────────────
        $productIds = $soldRows->keys()->merge($returnedRows->keys())->unique()->values();

        $products = Product::with('unit')
            ->with(['branches' => function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }])
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get();

        // ── بناء صفوف التقرير ──────────────────────────────────────
        $rows = $products->map(function ($product) use ($soldRows, $returnedRows, $branchId) {
            $sold     = $soldRows->get($product->id);
            $returned = $returnedRows->get($product->id);

            $qtySold        = (float) ($sold->qty_sold ?? 0);
            $salesTotal     = (float) ($sold->sales_total ?? 0);
            $costTotal      = (float) ($sold->cost_total ?? 0);
            $qtyMissingCost = (float) ($sold->qty_missing_cost ?? 0);
            $invoices       = (int)   ($sold->invoices_count ?? 0);

            $qtyReturned = (float) ($returned->qty_returned ?? 0);
            $refundTotal = (float) ($returned->refund_total ?? 0);

            // البنود القديمة التي لم تُسجّل سعر تكلفة وقت البيع: نكمّل تكلفتها
            // من تكلفة الفرع الحالية (أو تكلفة المنتج العامة) حتى لا تكون التكلفة صفراً.
            if ($qtyMissingCost > 0) {
                $fallbackCost = $branchId
                    ? $product->getCostInBranch((int) $branchId)
                    : (float) $product->cost_price;
                $costTotal += $fallbackCost * $qtyMissingCost;
            }

            // متوسط سعر البيع والشراء للوحدة
            $avgSellPrice = $qtySold > 0 ? $salesTotal / $qtySold : 0;
            $avgCostPrice = $qtySold > 0 ? $costTotal  / $qtySold : (float) $product->cost_price;

            // الكمية المتبقية في المخزون
            $remainingQty = $product->branches->sum(fn($b) => (float) ($b->pivot->quantity ?? 0));

            // صافي الربح = (مبيعات − تكلفة المبيعات) − مرتجعات
            $grossProfit = $salesTotal - $costTotal;
            $netProfit   = $grossProfit - $refundTotal;

            return [
                'product'       => $product,
                'avgCostPrice'  => $avgCostPrice,
                'avgSellPrice'  => $avgSellPrice,
                'qtySold'       => $qtySold,
                'qtyReturned'   => $qtyReturned,
                'qtyNet'        => $qtySold - $qtyReturned,
                'remainingQty'  => $remainingQty,
                'salesTotal'    => $salesTotal,
                'costTotal'     => $costTotal,
                'refundTotal'   => $refundTotal,
                'grossProfit'   => $grossProfit,
                'netProfit'     => $netProfit,
                'invoices'      => $invoices,
            ];
        })->sortByDesc('netProfit')->values();

        // ── الإجماليات ─────────────────────────────────────────────
        $totals = [
            'salesTotal'  => $rows->sum('salesTotal'),
            'costTotal'   => $rows->sum('costTotal'),
            'refundTotal' => $rows->sum('refundTotal'),
            'grossProfit' => $rows->sum('grossProfit'),
            'netProfit'   => $rows->sum('netProfit'),
            'qtySold'     => $rows->sum('qtySold'),
            'qtyReturned' => $rows->sum('qtyReturned'),
        ];

        return view('pages.reports.product-profit', compact(
            'dateFrom', 'dateTo', 'branchId', 'branches', 'scopedBranchId',
            'rows', 'totals',
            'activeOrdersCount', 'cancelledOrdersCount', 'returnsCount',
        ));
    }

    public function financialOverview(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   now()->format('Y-m-d'));

        // ══════════════════════════════════════════════════════════
        // 1. CASH — فلوس برة (الخزن)
        // ══════════════════════════════════════════════════════════
        $treasuries = Treasury::where('is_active', true)->orderBy('name')->get();
        $totalCash  = $treasuries->sum('balance');

        // ══════════════════════════════════════════════════════════
        // 2. RECEIVABLES — فلوس عند العملاء
        // ══════════════════════════════════════════════════════════

        // رصيد الافتتاحي على العملاء
        $customerOpeningBalance = (float) Customer::sum('opening_balance');

        // ذمم الطلبات النشطة (confirmed/partial_paid فقط، بدون فلتر تاريخ)
        $activeOrdersOutstanding = (float) (SaleOrder::whereIn('status', ['confirmed', 'partial_paid'])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as outstanding')
            ->value('outstanding') ?? 0);

        // المرتجعات المؤكدة تُخصم من الذمم
        $customerReturnsTotal = (float) SaleReturn::whereIn('status', ['confirmed', 'refunded'])
            ->whereNull('deleted_at')
            ->sum('refund_amount');

        $customerReceivables = max(0,
            $customerOpeningBalance + $activeOrdersOutstanding - $customerReturnsTotal
        );

        $installmentReceivables = InstallmentPlan::where('party_type', 'customer')
            ->where('status', 'active')
            ->get()
            ->sum(fn($p) => (float)($p->outstanding ?? 0));

        $totalReceivables = $customerReceivables + $installmentReceivables;

        // ══════════════════════════════════════════════════════════
        // 3. INVENTORY — البضاعة اللي عندنا
        // ══════════════════════════════════════════════════════════
        $products = Product::with('branches')->get();
        $inventoryValue = $products->sum(function ($p) {
            $qty = $p->branches->sum(fn($b) => (float)($b->pivot->quantity ?? 0));
            return $qty * (float)$p->cost_price;
        });
        $inventoryCount = $products->sum(function ($p) {
            return $p->branches->sum(fn($b) => (float)($b->pivot->quantity ?? 0));
        });

        // ══════════════════════════════════════════════════════════
        // 4. PURCHASES — اشترينا بكام (في الفترة)
        // ══════════════════════════════════════════════════════════
        $purchaseCost = PurchaseInvoice::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('total');

        $purchaseReturns = PurchaseReturn::where('status', 'completed')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('refund_amount');

        $netPurchases = $purchaseCost - $purchaseReturns;

        // What we still owe suppliers
        $supplierPayables = PurchaseInvoice::whereIn('status', ['confirmed', 'partial_paid'])
            ->get()
            ->sum(fn($i) => max(0, (float)$i->total - (float)$i->paid_amount));

        // ══════════════════════════════════════════════════════════
        // 5. SALES — بعنا بكام (في الفترة)
        // ══════════════════════════════════════════════════════════
        $salesRevenue = SaleOrder::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('total');

        $salesCollected = SaleOrder::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('paid_amount');

        $salesReturns = SaleReturn::where('status', 'completed')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('refund_amount');

        $netSales = $salesRevenue - $salesReturns;

        // ══════════════════════════════════════════════════════════
        // 6. OTHER REVENUES — إيرادات أخرى
        // ══════════════════════════════════════════════════════════
        $otherRevenues = FinancialTransaction::where('type', 'revenue')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
        $otherRevenuesTotal = $otherRevenues->sum('total');

        $totalRevenue = $netSales + $otherRevenuesTotal;

        // ══════════════════════════════════════════════════════════
        // 7. EXPENSES — صرفنا كام
        // ══════════════════════════════════════════════════════════
        $expenseLines = FinancialTransaction::where('type', 'expense')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
        $totalExpenses = $expenseLines->sum('total');

        // ══════════════════════════════════════════════════════════
        // 8. DEPRECIATION — إهلاك البضاعة
        // ══════════════════════════════════════════════════════════
        $depreciations = ProductDepreciation::where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();
        $totalDepreciation = $depreciations->sum('total_loss');

        // ══════════════════════════════════════════════════════════
        // 9. PROFIT / LOSS — الربح والخسارة
        // ══════════════════════════════════════════════════════════
        $grossProfit = $netSales - $netPurchases;
        $netProfit   = $totalRevenue - $netPurchases - $totalExpenses - $totalDepreciation;

        // ══════════════════════════════════════════════════════════
        // 10. OPENING BALANCES — الأرصدة الافتتاحية
        // ══════════════════════════════════════════════════════════

        // Customers — رصيد العملاء الجاري (balance column)
        // $customerOpeningBalance already computed in section 2
        $customerCurrentBalance = Customer::sum('balance');

        // Suppliers — ذمم افتتاحية للموردين
        $supplierOpeningBalance = Supplier::sum('opening_balance');
        // Suppliers — رصيد الموردين الجاري
        $supplierCurrentBalance = Supplier::sum('balance');

        // Delegates — فلوس المناديب
        $delegates = Delegate::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'cash_custody', 'total_collected', 'total_due']);
        $delegateCashCustody  = $delegates->sum('cash_custody');   // نقدية عند المناديب (أصل)
        $delegateNetReceivable = $delegates->sum(fn($d) =>          // صافي ما يستحقه المندوب لنا
            max(0, (float)$d->total_due - (float)$d->total_collected)
        );

        // Accounts — رصيد كل حساب من حركات المالية (كل الوقت)
        $accountBalances = Account::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($account) {
                $revenues = FinancialTransaction::where('account_id', $account->id)
                    ->where('type', 'revenue')->sum('amount');
                $expenses = FinancialTransaction::where('account_id', $account->id)
                    ->where('type', 'expense')->sum('amount');
                $account->net_balance = (float)$revenues - (float)$expenses;
                return $account;
            });

        // ══════════════════════════════════════════════════════════
        // 11. TOTAL NET WORTH — صافي الوضع المالي
        // ══════════════════════════════════════════════════════════
        // $totalReceivables already includes $customerOpeningBalance
        $totalAssets = $totalCash
            + $totalReceivables
            + $delegateCashCustody
            + $delegateNetReceivable
            + $inventoryValue;

        $totalLiabilities = $supplierPayables + $supplierOpeningBalance;
        $netWorth = $totalAssets - $totalLiabilities;

        return view('pages.reports.financial-overview', compact(
            'dateFrom', 'dateTo',
            // Cash
            'treasuries', 'totalCash',
            // Receivables
            'customerOpeningBalance', 'activeOrdersOutstanding', 'customerReturnsTotal',
            'customerReceivables', 'installmentReceivables', 'totalReceivables',
            // Inventory
            'inventoryValue', 'inventoryCount',
            // Purchases
            'purchaseCost', 'purchaseReturns', 'netPurchases', 'supplierPayables',
            // Sales
            'salesRevenue', 'salesCollected', 'salesReturns', 'netSales',
            // Revenues
            'otherRevenues', 'otherRevenuesTotal', 'totalRevenue',
            // Expenses
            'expenseLines', 'totalExpenses',
            // Depreciation
            'depreciations', 'totalDepreciation',
            // Profit
            'grossProfit', 'netProfit',
            // Opening Balances
            'customerOpeningBalance', 'customerCurrentBalance',
            'supplierOpeningBalance', 'supplierCurrentBalance',
            'delegates', 'delegateCashCustody', 'delegateNetReceivable',
            'accountBalances',
            // Summary
            'totalAssets', 'totalLiabilities', 'netWorth',
        ));
    }
}
