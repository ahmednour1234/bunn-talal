<?php

namespace App\Http\Controllers;

use App\Models\ProductDepreciation;
use Mpdf\Mpdf;

class ProductDepreciationController extends Controller
{
    public function index()
    {
        return view('pages.product-depreciations.index');
    }

    public function create()
    {
        return view('pages.product-depreciations.create');
    }

    public function show(int $id)
    {
        return view('pages.product-depreciations.show', ['id' => $id]);
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $depreciation = ProductDepreciation::with([
            'branch',
            'admin',
            'approvedByAdmin',
            'items.product.unit',
            'items.unit',
        ])->findOrFail($id);

        $html = view('pdf.product-depreciation-single', [
            'title'       => 'طلب إهلاك #' . $depreciation->depreciation_number,
            'depreciation' => $depreciation,
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

        $filename = 'إهلاك_' . $depreciation->depreciation_number . '.pdf';

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
