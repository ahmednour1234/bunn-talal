<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductTemplateExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportExportController extends Controller
{
    public function template()
    {
        return Excel::download(new ProductTemplateExport(), 'product-template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new ProductsImport(), $request->file('file'));
            return redirect()->route('products.index')->with('success', 'تم استيراد المنتجات بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport(), 'products.xlsx');
    }
}
