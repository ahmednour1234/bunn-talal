<?php

namespace App\Services;

use App\Models\HrSalary;
use App\Models\TreasuryTransaction;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrSalaryService
{
    public function all(array $filters = [])
    {
        $q = HrSalary::with(['delegate', 'account', 'treasury'])
            ->orderByDesc('year')
            ->orderByDesc('month');

        if (!empty($filters['delegate_id'])) {
            $q->where('delegate_id', $filters['delegate_id']);
        }

        if (!empty($filters['year'])) {
            $q->where('year', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $q->where('month', $filters['month']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->paginate(15);
    }

    public function getById(int $id): HrSalary
    {
        return HrSalary::with(['delegate', 'account', 'treasury'])->findOrFail($id);
    }

    public function create(array $data): HrSalary
    {
        $data['admin_id'] = Auth::guard('admin')->id();
        return HrSalary::create($data);
    }

    public function update(int $id, array $data): HrSalary
    {
        $salary = HrSalary::findOrFail($id);
        $salary->update($data);
        return $salary;
    }

    public function pay(int $id): HrSalary
    {
        return DB::transaction(function () use ($id) {
            $salary = HrSalary::with(['delegate', 'account', 'treasury'])->findOrFail($id);
            $adminId = Auth::guard('admin')->id();
            $net = $salary->basic_salary + $salary->commissions + $salary->bonuses - $salary->deductions;

            // Record financial transaction linked to account (salary expense)
            if ($salary->account_id) {
                FinancialTransaction::create([
                    'type'        => 'expense',
                    'account_id'  => $salary->account_id,
                    'treasury_id' => $salary->treasury_id,
                    'amount'      => $net,
                    'description' => 'راتب مندوب: ' . $salary->delegate->name . ' - ' . $salary->month_label . ' ' . $salary->year,
                    'date'        => now()->toDateString(),
                    'admin_id'    => $adminId,
                ]);
            }

            // Debit treasury if set
            if ($salary->treasury_id) {
                TreasuryTransaction::create([
                    'treasury_id'      => $salary->treasury_id,
                    'type'             => 'withdrawal',
                    'amount'           => $net,
                    'description'      => 'صرف راتب: ' . $salary->delegate->name . ' - ' . $salary->month_label . ' ' . $salary->year,
                    'date'             => now()->toDateString(),
                    'reference_number' => 'SAL-' . $salary->id,
                    'admin_id'         => $adminId,
                ]);
            }

            $salary->update([
                'status'  => 'paid',
                'paid_at' => now()->toDateString(),
            ]);

            return $salary;
        });
    }

    public function delete(int $id): void
    {
        $salary = HrSalary::findOrFail($id);
        if ($salary->status === 'paid') {
            throw new \Exception('لا يمكن حذف راتب تم صرفه');
        }
        $salary->delete();
    }
}
