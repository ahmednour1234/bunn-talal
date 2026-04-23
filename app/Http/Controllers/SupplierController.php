<?php

namespace App\Http\Controllers;

class SupplierController extends Controller
{
    public function index()
    {
        return view('pages.suppliers.index');
    }

    public function create()
    {
        return view('pages.suppliers.create');
    }

    public function edit(int $id)
    {
        return view('pages.suppliers.edit', compact('id'));
    }
}
