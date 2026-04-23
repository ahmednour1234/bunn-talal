<?php

namespace App\Http\Controllers;

class MeasurementUnitController extends Controller
{
    public function index()
    {
        return view('pages.units.index');
    }

    public function create()
    {
        return view('pages.units.create');
    }

    public function edit(int $id)
    {
        return view('pages.units.edit', compact('id'));
    }
}
