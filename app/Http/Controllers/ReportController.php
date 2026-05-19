<?php

namespace App\Http\Controllers;

use App\Models\Account;
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
        $customerReceivables = SaleOrder::whereIn('status', ['confirmed', 'partial_paid'])
            ->get()
            ->sum(fn($o) => max(0, (float)$o->total - (float)$o->paid_amount));

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

        // Customers — ذمم افتتاحية على العملاء
        $customerOpeningBalance = Customer::sum('opening_balance');
        // Customers — رصيد العملاء الجاري (balance column)
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
        $totalAssets = $totalCash
            + $totalReceivables
            + $customerOpeningBalance
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
