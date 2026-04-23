<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use Mpdf\Mpdf;

class SaleReturnController extends Controller
{
    public function index()
    {
        return view('pages.sale-returns.index');
    }

    public function create()
    {
        return view('pages.sale-returns.create');
    }

    public function show(int $id)
    {
        return view('pages.sale-returns.show', ['id' => $id]);
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $return = SaleReturn::with([
            'customer',
            'branch',
            'admin',
            'treasury',
            'order',
            'items.product',
            'items.unit',
        ])->findOrFail($id);

        $html = view('pdf.sale-return-single', [
            'title'  => 'مرتجع مبيعات - ' . $return->return_number,
            'return' => $return,
        ])->render();

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

        $filename = 'مرتجع_' . $return->return_number . '.pdf';

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}

