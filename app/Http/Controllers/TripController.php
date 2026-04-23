<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TripController extends Controller
{
    public function index()
    {
        return view('pages.trips.index');
    }

    public function create()
    {
        return view('pages.trips.create');
    }

    public function edit(int $id)
    {
        return view('pages.trips.edit', ['id' => $id]);
    }

    public function show(int $id)
    {
        return view('pages.trips.show', ['id' => $id]);
    }

    public function settle(int $id)
    {
        return view('pages.trips.settle', ['id' => $id]);
    }

    public function bookingRequests()
    {
        return view('pages.booking-requests.index');
    }

    public function createBookingRequest(\Illuminate\Http\Request $request)
    {
        $tripId = $request->integer('trip_id') ?: null;
        return view('pages.booking-requests.create', compact('tripId'));
    }

    public function pdf(int $id): Response
    {
        $trip = Trip::with([
            'delegate',
            'branch',
            'admin',
            'settler',
            'dispatches.branch',
            'saleOrders.customer',
            'collections.customer',
            'saleReturns.customer',
        ])->findOrFail($id);

        $trip->syncTotals();

        $pdf = Pdf::loadView('pdf.trip', compact('trip'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        return $pdf->download("trip-{$trip->trip_number}.pdf");
    }
}
