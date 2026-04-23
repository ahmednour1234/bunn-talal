<?php

namespace App\Http\Controllers;

use App\Exports\AccountsExport;
use App\Exports\FinancialTransactionsExport;
use App\Exports\TreasuriesExport;
use App\Exports\TreasuryTransactionsExport;
use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class AccountingExportController extends Controller
{
    // ─── Excel Exports ───

    public function accountsExcel()
    {
        return Excel::download(new AccountsExport, 'الحسابات.xlsx');
    }

    public function treasuriesExcel()
    {
        return Excel::download(new TreasuriesExport, 'الخزن.xlsx');
    }

    public function treasuryTransactionsExcel(Request $request)
    {
        return Excel::download(
            new TreasuryTransactionsExport(
                $request->get('search') ?? '',
                $request->get('treasury') ?? '',
                $request->get('type') ?? '',
            ),
            'حركات_الخزن.xlsx'
        );
    }

    public function financialTransactionsExcel(Request $request)
    {
        return Excel::download(
            new FinancialTransactionsExport(
                $request->get('search') ?? '',
                $request->get('type') ?? '',
                $request->get('account') ?? '',
            ),
            'المصروفات_والإيرادات.xlsx'
        );
    }

    // ─── PDF Helper ───

    protected function generatePdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'autoArabic' => true,
            'autoLangToFont' => true,
            'default_font' => 'XB Riyaz',
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ─── PDF Exports ───

    public function accountsPdf()
    {
        $data = Account::orderBy('id')->get();

        return $this->generatePdf('pdf.accounts', [
            'title' => 'تقرير الحسابات',
            'data' => $data,
        ], 'الحسابات.pdf');
    }

    public function treasuriesPdf()
    {
        $data = Treasury::orderBy('id')->get();

        return $this->generatePdf('pdf.treasuries', [
            'title' => 'تقرير الخزن',
            'data' => $data,
        ], 'الخزن.pdf');
    }

    public function treasuryTransactionsPdf(Request $request)
    {
        $query = TreasuryTransaction::query()->with(['treasury', 'admin']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        if ($treasury = $request->get('treasury')) {
            $query->where('treasury_id', $treasury);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $data = $query->latest()->get();

        return $this->generatePdf('pdf.treasury-transactions', [
            'title' => 'تقرير حركات الخزن',
            'data' => $data,
        ], 'حركات_الخزن.pdf');
    }

    public function financialTransactionsPdf(Request $request)
    {
        $query = FinancialTransaction::query()->with(['account', 'treasury', 'admin']);

        if ($search = $request->get('search')) {
            $query->where('description', 'like', "%{$search}%");
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($account = $request->get('account')) {
            $query->where('account_id', $account);
        }

        $data = $query->latest()->get();

        return $this->generatePdf('pdf.financial-transactions', [
            'title' => 'تقرير المصروفات والإيرادات',
            'data' => $data,
        ], 'المصروفات_والإيرادات.pdf');
    }

    public function reportsPdf(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $financialQuery = FinancialTransaction::query()
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo);

        $treasuryTxQuery = TreasuryTransaction::query()
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo);

        $summary = [
            'totalTreasuryBalance' => Treasury::where('is_active', true)->sum('balance'),
            'totalExpenses' => (clone $financialQuery)->where('type', 'expense')->sum('amount'),
            'totalRevenues' => (clone $financialQuery)->where('type', 'revenue')->sum('amount'),
            'totalDeposits' => (clone $treasuryTxQuery)->where('type', 'deposit')->sum('amount'),
            'totalWithdrawals' => (clone $treasuryTxQuery)->where('type', 'withdrawal')->sum('amount'),
            'treasuryBalances' => Treasury::where('is_active', true)->orderBy('name')->get(),
            'expensesByAccount' => FinancialTransaction::where('type', 'expense')
                ->where('date', '>=', $dateFrom)->where('date', '<=', $dateTo)
                ->selectRaw('account_id, SUM(amount) as total')
                ->groupBy('account_id')->with('account')->get(),
            'revenuesByAccount' => FinancialTransaction::where('type', 'revenue')
                ->where('date', '>=', $dateFrom)->where('date', '<=', $dateTo)
                ->selectRaw('account_id, SUM(amount) as total')
                ->groupBy('account_id')->with('account')->get(),
        ];

        $data = FinancialTransaction::with(['account', 'admin'])
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->latest()->get();

        return $this->generatePdf('pdf.reports', [
            'title' => "التقارير المالية ({$dateFrom} — {$dateTo})",
            'data' => $data,
            'summary' => $summary,
        ], 'التقارير_المالية.pdf');
    }
}
