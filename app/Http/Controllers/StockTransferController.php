<?php

namespace App\Http\Controllers;

class StockTransferController extends Controller
{
    public function index()
    {
        return view('pages.stock-transfers.index');
    }

    public function create()
    {
        return view('pages.stock-transfers.create');
    }
}
