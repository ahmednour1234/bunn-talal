<?php

namespace App\Http\Controllers;

class VehicleController extends Controller
{
    public function index()
    {
        return view('pages.vehicles.index');
    }

    public function create()
    {
        return view('pages.vehicles.create');
    }

    public function edit(int $id)
    {
        return view('pages.vehicles.edit', compact('id'));
    }
}
