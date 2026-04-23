<?php

namespace App\Http\Controllers;

class BranchController extends Controller
{
    public function index()
    {
        return view('pages.branches.index');
    }

    public function create()
    {
        return view('pages.branches.create');
    }

    public function edit(int $id)
    {
        return view('pages.branches.edit', ['id' => $id]);
    }
}
