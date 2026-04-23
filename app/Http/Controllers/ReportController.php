<?php

namespace App\Http\Controllers;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.reports.index');
    }

    public function incomeStatement()
    {
        return view('pages.reports.income-statement');
    }

    public function accountStatement()
    {
        return view('pages.reports.account-statement');
    }

    public function balanceSheet()
    {
        return view('pages.reports.balance-sheet');
    }
}
