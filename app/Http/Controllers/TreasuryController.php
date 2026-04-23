<?php

namespace App\Http\Controllers;

class TreasuryController extends Controller
{
    public function index()
    {
        return view('pages.treasuries.index');
    }

    public function create()
    {
        return view('pages.treasuries.create');
    }

    public function edit(int $id)
    {
        return view('pages.treasuries.edit', compact('id'));
    }
}
