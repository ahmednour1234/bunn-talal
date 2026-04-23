<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.products.index');
    }

    public function create()
    {
        return view('pages.products.create');
    }

    public function edit(int $id)
    {
        return view('pages.products.edit', ['id' => $id]);
    }
}
