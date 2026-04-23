<?php

namespace App\Http\Controllers;

use App\Models\InventoryDispatch;
use Mpdf\Mpdf;

class InventoryDispatchController extends Controller
{
    public function index()
    {
        return view('pages.inventory-dispatches.index');
    }

    public function create()
    {
        return view('pages.inventory-dispatches.create');
    }

    public function show(int $id)
    {
        return view('pages.inventory-dispatches.show', ['id' => $id]);
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $dispatch = InventoryDispatch::with([
            'branch',
            'delegate',
            'admin',
            'items.product',
        ])->findOrFail($id);

        $html = view('pdf.inventory-dispatch-single', [
            'title' => 'أمر صرف مخزني #' . $dispatch->id,
            'dispatch' => $dispatch,
        ])->render();

        $mpdf = new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4-L',
            'autoArabic'       => true,
            'autoLangToFont'   => true,
            'default_font'     => 'XB Riyaz',
            'tempDir'          => storage_path('app/mpdf'),
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $filename = 'صرف_مخزني_' . $dispatch->id . '.pdf';

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
