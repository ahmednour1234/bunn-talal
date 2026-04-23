<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        return view('pages.collections.index');
    }

    public function create()
    {
        return view('pages.collections.create');
    }

    public function show(int $id)
    {
        return view('pages.collections.show', compact('id'));
    }
}
