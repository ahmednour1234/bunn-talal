<?php

namespace App\Http\Controllers;

class RoleController extends Controller
{
    public function index()
    {
        return view('pages.roles.index');
    }

    public function create()
    {
        return view('pages.roles.create');
    }

    public function edit(int $id)
    {
        return view('pages.roles.edit', ['id' => $id]);
    }
}
