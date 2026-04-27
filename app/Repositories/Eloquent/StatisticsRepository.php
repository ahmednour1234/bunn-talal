<?php

namespace App\Repositories\Eloquent;

use App\Models\Collection;
use App\Models\HrAttendance;
use App\Models\HrLeave;
use App\Models\HrSalary;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use App\Models\Trip;
use App\Repositories\Contracts\StatisticsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class StatisticsRepository implements StatisticsRepositoryInterface
{
    public function delegateStatistics(int $delegateId, array $filters = []): array
    {
        $totalSales = $this->applyDateFilters(
            SaleOrder::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $filters, 'date'
        )->sum('total');

        $totalCollections = $this->applyDateFilters(
            Collection::where('delegate_id', $delegateId)->where('status', 'completed'),
            $filters, 'collection_date'
        )->sum('total_amount');

        $totalReturns = $this->applyDateFilters(
            SaleReturn::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $filters, 'date'
        )->sum('refund_amount');

        $tripsCount = $this->applyDateFilters(
            Trip::where('delegate_id', $delegateId),
            $filters, 'start_date'
        )->count();

        $ordersCount = $this->applyDateFilters(
            SaleOrder::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $filters, 'date'
        )->count();

        $collectionsCount = $this->applyDateFilters(
            Collection::where('delegate_id', $delegateId)->where('status', 'completed'),
            $filters, 'collection_date'
        )->count();

        $returnsCount = $this->applyDateFilters(
            SaleReturn::where('delegate_id', $delegateId)->whereNotIn('status', ['cancelled']),
            $filters, 'date'
        )->count();

        return [
            'total_sales'        => (float) $totalSales,
            'total_collections'  => (float) $totalCollections,
            'total_returns'      => (float) $totalReturns,
            'trips_count'        => $tripsCount,
            'orders_count'       => $ordersCount,
            'collections_count'  => $collectionsCount,
            'returns_count'      => $returnsCount,
        ];
    }

    public function delegateHrStatistics(int $delegateId, array $filters = []): array
    {
        // Attendance
        $presentDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'present'),
            $filters, 'date'
        )->count();

        $absentDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'absent'),
            $filters, 'date'
        )->count();

        $lateDays = $this->applyDateFilters(
            HrAttendance::where('delegate_id', $delegateId)->where('status', 'late'),
            $filters, 'date'
        )->count();

        // Leaves
        $totalLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId),
            $filters, 'start_date'
        )->count();

        $approvedLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'approved'),
            $filters, 'start_date'
        )->count();

        $pendingLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'pending'),
            $filters, 'start_date'
        )->count();

        $rejectedLeaves = $this->applyDateFilters(
            HrLeave::where('delegate_id', $delegateId)->where('status', 'rejected'),
            $filters, 'start_date'
        )->count();

        // Salaries â€” use dedicated month/year columns
        $salaryQuery = HrSalary::where('delegate_id', $delegateId)->where('status', 'paid');

        if (!empty($filters['month'])) {
            $salaryQuery->where('month', $filters['month']);
        }
        if (!empty($filters['year'])) {
            $salaryQuery->where('year', $filters['year']);
        }

        $totalSalaries    = (clone $salaryQuery)->sum('basic_salary');
        $totalCommissions = (clone $salaryQuery)->sum('commissions');
        $totalBonuses     = (clone $salaryQuery)->sum('bonuses');
        $totalDeductions  = (clone $salaryQuery)->sum('deductions');

        return [
            'present_days'    => $presentDays,
            'absent_days'     => $absentDays,
            'late_days'       => $lateDays,
            'total_leaves'    => $totalLeaves,
            'approved_leaves' => $approvedLeaves,
            'pending_leaves'  => $pendingLeaves,
            'rejected_leaves' => $rejectedLeaves,
            'total_salaries'  => (float) $totalSalaries,
            'total_commissions' => (float) $totalCommissions,
            'total_bonuses'   => (float) $totalBonuses,
            'total_deductions' => (float) $totalDeductions,
        ];
    }

    private function applyDateFilters(Builder $query, array $filters, string $column): Builder
    {
        if (!empty($filters['date'])) {
            $query->whereDate($column, $filters['date']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear($column, $filters['year']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth($column, $filters['month']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate($column, '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate($column, '<=', $filters['to_date']);
        }

        return $query;
    }
}
