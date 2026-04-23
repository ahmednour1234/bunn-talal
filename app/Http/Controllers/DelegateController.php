<?php

namespace App\Http\Controllers;

class DelegateController extends Controller
{
    public function index()
    {
        return view('pages.delegates.index');
    }

    public function create()
    {
        return view('pages.delegates.create');
    }

    public function show(int $id)
    {
        return view('pages.delegates.show', compact('id'));
    }

    public function edit(int $id)
    {
        return view('pages.delegates.edit', compact('id'));
    }
}
