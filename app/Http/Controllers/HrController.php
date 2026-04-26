<?php

namespace App\Http\Controllers;

class HrController extends Controller
{
    // ─── Leaves ─────────────────────────────────────────────
    public function leavesIndex()
    {
        return view('pages.hr.leaves.index');
    }

    public function leavesCreate()
    {
        return view('pages.hr.leaves.create');
    }

    public function leavesEdit(int $id)
    {
        return view('pages.hr.leaves.edit', compact('id'));
    }

    // ─── Attendance ──────────────────────────────────────────
    public function attendanceIndex()
    {
        return view('pages.hr.attendance.index');
    }

    public function attendanceCreate()
    {
        return view('pages.hr.attendance.create');
    }

    public function attendanceEdit(int $id)
    {
        return view('pages.hr.attendance.edit', compact('id'));
    }

    // ─── Salaries ────────────────────────────────────────────
    public function salariesIndex()
    {
        return view('pages.hr.salaries.index');
    }

    public function salariesCreate()
    {
        return view('pages.hr.salaries.create');
    }

    public function salariesEdit(int $id)
    {
        return view('pages.hr.salaries.edit', compact('id'));
    }
}
