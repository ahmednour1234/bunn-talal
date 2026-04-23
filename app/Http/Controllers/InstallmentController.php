<?php

namespace App\Http\Controllers;

class InstallmentController extends Controller
{
    public function index()
    {
        return view('pages.installments.index');
    }

    public function create()
    {
        return view('pages.installments.create');
    }

    public function show(int $id)
    {
        return view('pages.installments.show', ['id' => $id]);
    }
}
