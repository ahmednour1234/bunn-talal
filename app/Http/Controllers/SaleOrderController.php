<?php

namespace App\Http\Controllers;

use App\Models\SaleOrder;
use App\Services\SaleOrderService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class SaleOrderController extends Controller
{
    public function index()
    {
        return view('pages.sale-orders.index');
    }

    public function create()
    {
        return view('pages.sale-orders.create');
    }

    public function show(int $id)
    {
        return view('pages.sale-orders.show', ['id' => $id]);
    }

    public function showPdf(int $id): \Illuminate\Http\Response
    {
        $order = SaleOrder::with([
            'customer',
            'branch',
            'delegate',
            'admin',
            'treasury',
            'items.product',
            'items.unit',
            'payments.treasury',
            'payments.admin',
        ])->findOrFail($id);

        return $this->generatePdf('pdf.sale-order-single', [
            'title' => 'طلب مبيعات - ' . $order->order_number,
            'order' => $order,
        ], 'طلب_' . $order->order_number . '.pdf');
    }

    protected function generatePdf(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'autoArabic'       => true,
            'autoLangToFont'   => true,
            'default_font'     => 'XB Riyaz',
            'tempDir'          => storage_path('app/mpdf'),
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
