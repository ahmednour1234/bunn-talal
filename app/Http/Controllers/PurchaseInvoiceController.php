<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Services\PurchaseInvoiceService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        return view('pages.purchase-invoices.index');
    }

    public function create()
    {
        return view('pages.purchase-invoices.create');
    }

    public function show(int $id)
    {
        return view('pages.purchase-invoices.show', ['id' => $id]);
    }

    public function edit(int $id)
    {
        return view('pages.purchase-invoices.edit', ['id' => $id]);
    }

    public function exportPdf(Request $request, PurchaseInvoiceService $service)
    {
        $search = $request->get('search') ?: null;
        $status = $request->get('status') ?: null;
        $supplierId = $request->filled('supplier') ? (int) $request->get('supplier') : null;
        $branchId = $request->filled('branch') ? (int) $request->get('branch') : null;

        $data = $service->getFilteredInvoices($search, $status, $supplierId, $branchId);

        return $this->generatePdf('pdf.purchase-invoices', [
            'title' => 'تقرير فواتير المشتريات',
            'data' => $data,
            'statusLabels' => PurchaseInvoice::statusLabels(),
        ], 'فواتير_المشتريات.pdf');
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
