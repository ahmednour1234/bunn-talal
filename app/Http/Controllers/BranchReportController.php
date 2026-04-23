<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchReportService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class BranchReportController extends Controller
{
    public function inventory()
    {
        return view('pages.reports.branch-inventory');
    }

    public function movements()
    {
        return view('pages.reports.branch-movements');
    }

    public function inventoryPdf(Request $request, BranchReportService $service)
    {
        $branchId = $request->filled('branch') ? (int) $request->get('branch') : null;
        $dateFrom = $request->get('date_from') ?: null;
        $dateTo = $request->get('date_to') ?: null;

        $inventory = $service->getBranchInventoryReport($branchId, $dateFrom, $dateTo);
        $summary = $service->getAllBranchesSummary($dateFrom, $dateTo);

        $branchName = $branchId
            ? (Branch::find($branchId)?->name ?? 'فرع غير معروف')
            : 'كل الفروع';

        $rangeText = ($dateFrom || $dateTo)
            ? (($dateFrom ?? '---') . ' - ' . ($dateTo ?? '---'))
            : 'كل الفترات';

        return $this->generatePdf('pdf.branch-inventory', [
            'title' => 'تقرير مخازن الفروع',
            'inventory' => $inventory,
            'summary' => $summary,
            'branchName' => $branchName,
            'rangeText' => $rangeText,
            'data' => $inventory,
        ], 'تقرير_مخازن_الفروع.pdf');
    }

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
}
