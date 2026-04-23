<?php

namespace App\Http\Controllers;

use App\Services\PurchaseReturnService;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        return view('pages.purchase-returns.index');
    }

    public function create()
    {
        return view('pages.purchase-returns.create');
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $return = PurchaseReturn::with([
            'supplier',
            'branch',
            'invoice',
            'treasury',
            'admin',
            'items.product',
            'items.unit',
        ])->findOrFail($id);

        return $this->generatePdf('pdf.purchase-return-single', [
            'title' => 'مرتجع مشتريات - ' . $return->return_number,
            'return' => $return,
            'data' => collect([$return]),
        ], 'مرتجع_' . $return->return_number . '.pdf');
    }

    public function exportPdf(Request $request, PurchaseReturnService $service)
    {
        $search = $request->get('search') ?: null;
        $status = $request->get('status') ?: null;
        $supplierId = $request->filled('supplier') ? (int) $request->get('supplier') : null;

        $data = $service->getFilteredReturns($search, $status, $supplierId);

        return $this->generatePdf('pdf.purchase-returns', [
            'title' => 'تقرير مرتجعات المشتريات',
            'data' => $data,
        ], 'مرتجعات_المشتريات.pdf');
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
