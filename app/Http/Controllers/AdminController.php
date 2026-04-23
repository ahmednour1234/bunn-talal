<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function index()
    {
        return view('pages.admins.index');
    }

    public function create()
    {
        return view('pages.admins.create');
    }

    public function edit(int $id)
    {
        return view('pages.admins.edit', ['id' => $id]);
    }
}
