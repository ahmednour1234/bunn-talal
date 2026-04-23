<?php

namespace App\Http\Controllers;

class TreasuryTransactionController extends Controller
{
    public function index()
    {
        return view('pages.treasury-transactions.index');
    }

    public function create()
    {
        return view('pages.treasury-transactions.create');
    }
}
