<?php

namespace App\Http\Controllers;

class CustomerController extends Controller
{
    public function index()
    {
        return view('pages.customers.index');
    }

    public function create()
    {
        return view('pages.customers.create');
    }

    public function edit(int $id)
    {
        return view('pages.customers.edit', compact('id'));
    }

    public function show(int $id)
    {
        return view('pages.customers.show', compact('id'));
    }
}
