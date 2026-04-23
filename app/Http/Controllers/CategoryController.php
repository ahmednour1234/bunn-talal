<?php

namespace App\Http\Controllers;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.categories.index');
    }

    public function create()
    {
        return view('pages.categories.create');
    }

    public function edit(int $id)
    {
        return view('pages.categories.edit', compact('id'));
    }
}
