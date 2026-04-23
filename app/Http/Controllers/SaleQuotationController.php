<?php

namespace App\Http\Controllers;

use App\Models\SaleQuotation;
use Mpdf\Mpdf;

class SaleQuotationController extends Controller
{
    public function index()
    {
        return view('pages.sale-quotations.index');
    }

    public function create()
    {
        return view('pages.sale-quotations.create');
    }

    public function show(int $id)
    {
        return view('pages.sale-quotations.show', ['id' => $id]);
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $quotation = SaleQuotation::with([
            'customer',
            'branch',
            'delegate',
            'admin',
            'items.product',
            'items.unit',
        ])->findOrFail($id);

        return $this->generatePdf('pdf.sale-quotation-single', [
            'title'     => 'عرض سعر - ' . $quotation->quotation_number,
            'quotation' => $quotation,
        ], 'عرض_' . $quotation->quotation_number . '.pdf');
    }

    protected function generatePdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'autoArabic'     => true,
            'autoLangToFont' => true,
            'default_font'   => 'XB Riyaz',
            'tempDir'        => storage_path('app/mpdf'),
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
