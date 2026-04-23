<?php

namespace App\Http\Controllers;

class FinancialTransactionController extends Controller
{
    public function index()
    {
        return view('pages.financial-transactions.index');
    }

    public function create()
    {
        return view('pages.financial-transactions.create');
    }

    public function edit(int $id)
    {
        return view('pages.financial-transactions.edit', compact('id'));
    }
}
