<?php

namespace App\Services;

use App\Repositories\Contracts\StatisticsRepositoryInterface;

class StatisticsService
{
    public function __construct(
        private readonly StatisticsRepositoryInterface $statisticsRepository
    ) {}

    public function getDelegateStatistics(int $delegateId, float $totalDue, float $basicSalary, float $cashCustody, array $filters = []): array
    {
        $data = $this->statisticsRepository->delegateStatistics($delegateId, $filters);

        return [
            ['label' => 'إجمالي المبيعات',          'value' => $data['total_sales']],
            ['label' => 'إجمالي المبيعات النقدية',  'value' => $data['cash_sales_total']],
            ['label' => 'إجمالي المبيعات الجزئية',  'value' => $data['partial_sales_total']],
            ['label' => 'إجمالي التحصيلات',         'value' => $data['total_collections']],
            ['label' => 'إجمالي النقدية المحصلة',   'value' => $data['cash_received']],
            ['label' => 'إجمالي المرتجعات',         'value' => $data['total_returns']],
            ['label' => 'المبلغ المستحق',            'value' => $totalDue],
            ['label' => 'إجمالي النقدية لديه',      'value' => $cashCustody],
            ['label' => 'الراتب الأساسي',           'value' => $basicSalary],
            ['label' => 'إجمالي العمولات',          'value' => $data['total_commissions']],
            ['label' => 'عدد الرحلات',              'value' => $data['trips_count']],
            ['label' => 'عدد أوامر البيع',          'value' => $data['orders_count']],
            ['label' => 'عدد التحصيلات',            'value' => $data['collections_count']],
            ['label' => 'عدد المرتجعات',            'value' => $data['returns_count']],
        ];
    }

    public function getDelegateHrStatistics(int $delegateId, array $filters = []): array
    {
        $data = $this->statisticsRepository->delegateHrStatistics($delegateId, $filters);

        return [
            ['label' => 'أيام الحضور',                  'value' => $data['present_days']],
            ['label' => 'أيام الغياب',                   'value' => $data['absent_days']],
            ['label' => 'أيام التأخير',                  'value' => $data['late_days']],
            ['label' => 'طلبات الإجازة',                 'value' => $data['total_leaves']],
            ['label' => 'الإجازات الموافق عليها',        'value' => $data['approved_leaves']],
            ['label' => 'الإجازات المعلقة',              'value' => $data['pending_leaves']],
            ['label' => 'الإجازات المرفوضة',             'value' => $data['rejected_leaves']],
            ['label' => 'إجمالي الرواتب المدفوعة',      'value' => $data['total_salaries']],
            ['label' => 'إجمالي العمولات',               'value' => $data['total_commissions']],
            ['label' => 'إجمالي البدلات',                'value' => $data['total_bonuses']],
            ['label' => 'إجمالي الخصومات',               'value' => $data['total_deductions']],
        ];
    }
}