<?php

namespace App\Http\Controllers;

class AccountController extends Controller
{
    public function index()
    {
        return view('pages.accounts.index');
    }

    public function create()
    {
        return view('pages.accounts.create');
    }

    public function edit(int $id)
    {
        return view('pages.accounts.edit', compact('id'));
    }
}
