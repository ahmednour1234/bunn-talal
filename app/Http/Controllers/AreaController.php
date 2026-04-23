<?php

namespace App\Http\Controllers;

class AreaController extends Controller
{
    public function index()
    {
        return view('pages.areas.index');
    }

    public function create()
    {
        return view('pages.areas.create');
    }

    public function edit(int $id)
    {
        return view('pages.areas.edit', compact('id'));
    }
}
