<?php

namespace App\Http\Controllers;

class TaxController extends Controller
{
    public function index()
    {
        return view('pages.taxes.index');
    }

    public function create()
    {
        return view('pages.taxes.create');
    }

    public function edit(int $id)
    {
        return view('pages.taxes.edit', ['id' => $id]);
    }
}
