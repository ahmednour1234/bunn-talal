<?php

namespace App\Support;

use App\Models\Collection;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;

/**
 * مديونية العميل — حساب الرصيد المستحق على العميل.
 *
 * يطابق منطق كشف الحساب في CustomerShow:
 *   الرصيد = رصيد افتتاحي + (مدين) - (دائن)
 *   المدين  = إجمالي الفواتير (عدا الملغاة) + دفعات صادرة للعميل
 *   الدائن  = المدفوع على الفواتير + المرتجعات + التحصيلات + عكس الفواتير الملغاة
 *
 * قيمة موجبة => العميل مدين لنا. قيمة سالبة => لنا رصيد لصالح العميل.
 */
class CustomerDebt
{
    /**
     * احسب مديونية مجموعة من العملاء دفعة واحدة (بدون N+1).
     *
     * @param  iterable<Customer>  $customers
     * @return array<int,float>  مفتاحه customer_id وقيمته المديونية
     */
    public static function forCustomers($customers): array
    {
        $ids = collect($customers)->pluck('id')->all();

        if (empty($ids)) {
            return [];
        }

        // مدين: إجمالي الفواتير غير الملغاة وغير المسودّة
        $invoiceDebit = SaleOrder::whereIn('customer_id', $ids)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(total) AS v')
            ->pluck('v', 'customer_id');

        // دائن: المدفوع على الفواتير غير المسودّة
        $invoicePaid = SaleOrder::whereIn('customer_id', $ids)
            ->whereNotIn('status', ['draft'])
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(paid_amount) AS v')
            ->pluck('v', 'customer_id');

        // مدين: دفعات صادرة للعميل
        $outgoingPayments = CustomerPayment::whereIn('customer_id', $ids)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(amount) AS v')
            ->pluck('v', 'customer_id');

        // دائن: المرتجعات المؤكّدة/المردودة
        $returns = SaleReturn::whereIn('customer_id', $ids)
            ->whereIn('status', ['confirmed', 'refunded'])
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(refund_amount) AS v')
            ->pluck('v', 'customer_id');

        // دائن: التحصيلات المكتملة
        $collections = Collection::whereIn('customer_id', $ids)
            ->where('status', 'completed')
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(total_amount) AS v')
            ->pluck('v', 'customer_id');

        $result = [];

        foreach ($customers as $customer) {
            $id = $customer->id;

            $debit = (float) ($invoiceDebit[$id] ?? 0)
                + (float) ($outgoingPayments[$id] ?? 0);

            $credit = (float) ($invoicePaid[$id] ?? 0)
                + (float) ($returns[$id] ?? 0)
                + (float) ($collections[$id] ?? 0);

            $result[$id] = (float) $customer->opening_balance + $debit - $credit;
        }

        return $result;
    }

    /**
     * احسب مديونية عميل واحد.
     */
    public static function forCustomer(Customer $customer): float
    {
        return self::forCustomers([$customer])[$customer->id] ?? 0.0;
    }
}
