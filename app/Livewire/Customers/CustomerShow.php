<?php

namespace App\Livewire\Customers;

use App\Models\Collection;
use App\Models\Customer;
use App\Models\InstallmentPlan;
use App\Models\SaleOrder;
use App\Models\SaleReturn;
use Livewire\Component;

class CustomerShow extends Component
{
    public int $customerId;
    public string $activeTab = 'overview';

    public function mount(int $id): void
    {
        $this->customerId = $id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $customer = Customer::with('area')->findOrFail($this->customerId);

        // ── Invoices (Sale Orders) ────────────────────────────────
        $orders = SaleOrder::where('customer_id', $this->customerId)
            ->with('branch', 'delegate')
            ->orderByDesc('date')
            ->get();

        $totalInvoiced  = $orders->whereNotIn('status', ['cancelled'])->sum('total');
        $totalPaid      = $orders->whereNotIn('status', ['cancelled'])->sum('paid_amount');
        $totalRemaining = $orders->whereIn('status', ['confirmed', 'partial_paid'])->sum(fn($o) => (float)$o->total - (float)$o->paid_amount);

        // ── Returns ───────────────────────────────────────────────
        $returns = SaleReturn::where('customer_id', $this->customerId)
            ->with('branch')
            ->orderByDesc('date')
            ->get();
        $totalReturns = $returns->where('status', 'completed')->sum('refund_amount');

        // ── Collections ───────────────────────────────────────────
        $collections = Collection::where('customer_id', $this->customerId)
            ->with('branch', 'delegate', 'treasury')
            ->orderByDesc('collection_date')
            ->get();
        $totalCollected = $collections->where('status', 'completed')->sum('total_amount');

        // ── Installment Plans ─────────────────────────────────────
        $installmentPlans = InstallmentPlan::where('party_type', 'customer')
            ->where('customer_id', $this->customerId)
            ->with('entries', 'branch')
            ->orderByDesc('created_at')
            ->get();

        $totalInstallmentAmount = $installmentPlans->whereNotIn('status', ['cancelled'])->sum('total_amount');
        $totalInstallmentPaid   = $installmentPlans->whereNotIn('status', ['cancelled'])->sum(fn($p) => $p->paid_amount);
        $totalInstallmentDue    = $installmentPlans->where('status', 'active')->sum(fn($p) => $p->outstanding);

        // ── Account Statement (Debit/Credit ledger) ────────────────
        $ledger = collect();

        foreach ($orders->whereNotIn('status', ['cancelled', 'draft']) as $o) {
            $ledger->push([
                'date'        => $o->date,
                'type'        => 'invoice',
                'reference'   => $o->order_number,
                'description' => 'فاتورة بيع',
                'debit'       => (float) $o->total,   // العميل مدين بقيمة الفاتورة
                'credit'      => 0,
            ]);
            if ((float) $o->paid_amount > 0) {
                $ledger->push([
                    'date'        => $o->date,
                    'type'        => 'payment',
                    'reference'   => $o->order_number,
                    'description' => 'دفعة على فاتورة',
                    'debit'       => 0,
                    'credit'      => (float) $o->paid_amount,
                ]);
            }
        }

        foreach ($returns->where('status', 'completed') as $r) {
            $ledger->push([
                'date'        => $r->date,
                'type'        => 'return',
                'reference'   => $r->return_number,
                'description' => 'مرتجع مبيعات',
                'debit'       => 0,
                'credit'      => (float) $r->refund_amount,
            ]);
        }

        foreach ($collections->where('status', 'completed') as $c) {
            $ledger->push([
                'date'        => $c->collection_date,
                'type'        => 'collection',
                'reference'   => $c->collection_number,
                'description' => 'تحصيل',
                'debit'       => 0,
                'credit'      => (float) $c->total_amount,
            ]);
        }

        $ledger = $ledger->sortBy('date')->values();

        // Running balance
        $runningBalance = (float) $customer->opening_balance;
        $ledgerWithBalance = $ledger->map(function ($row) use (&$runningBalance) {
            $runningBalance = $runningBalance + $row['debit'] - $row['credit'];
            return array_merge($row, ['balance' => $runningBalance]);
        });

        $currentBalance = $runningBalance; // positive = customer owes us

        // ── Credit Analysis ────────────────────────────────────────
        $analysis = $this->analyzeCustomer(
            $customer,
            $orders,
            $installmentPlans,
            $collections,
            $totalRemaining,
            $currentBalance
        );

        return view('livewire.customers.customer-show', [
            'customer'               => $customer,
            'orders'                 => $orders,
            'totalInvoiced'          => $totalInvoiced,
            'totalPaid'              => $totalPaid,
            'totalRemaining'         => $totalRemaining,
            'returns'                => $returns,
            'totalReturns'           => $totalReturns,
            'collections'            => $collections,
            'totalCollected'         => $totalCollected,
            'installmentPlans'       => $installmentPlans,
            'totalInstallmentAmount' => $totalInstallmentAmount,
            'totalInstallmentPaid'   => $totalInstallmentPaid,
            'totalInstallmentDue'    => $totalInstallmentDue,
            'ledger'                 => $ledgerWithBalance,
            'currentBalance'         => $currentBalance,
            'analysis'               => $analysis,
        ]);
    }

    private function analyzeCustomer(
        Customer $customer,
        $orders,
        $installmentPlans,
        $collections,
        float $totalRemaining,
        float $currentBalance
    ): array {
        $score = 0;
        $points = [];

        // 1. Payment timeliness from installments
        $activeOrCompletedPlans = $installmentPlans->whereIn('status', ['active', 'completed']);
        $totalEntries  = 0;
        $overdueCount  = 0;
        $onTimeCount   = 0;

        foreach ($activeOrCompletedPlans as $plan) {
            foreach ($plan->entries as $entry) {
                $totalEntries++;
                if ($entry->status === 'overdue') {
                    $overdueCount++;
                } elseif (in_array($entry->status, ['paid'])) {
                    $onTimeCount++;
                }
            }
        }

        if ($totalEntries > 0) {
            $overdueRate = $overdueCount / $totalEntries;
            if ($overdueRate === 0) {
                $score += 30;
                $points[] = ['icon' => '✅', 'text' => 'لا توجد أقساط متأخرة — ممتاز', 'positive' => true];
            } elseif ($overdueRate <= 0.1) {
                $score += 20;
                $points[] = ['icon' => '🟡', 'text' => 'نسبة تأخر منخفضة (' . round($overdueRate * 100) . '%)', 'positive' => true];
            } elseif ($overdueRate <= 0.3) {
                $score += 5;
                $points[] = ['icon' => '🟠', 'text' => 'نسبة تأخر متوسطة (' . round($overdueRate * 100) . '%)', 'positive' => false];
            } else {
                $score -= 10;
                $points[] = ['icon' => '🔴', 'text' => 'نسبة تأخر عالية (' . round($overdueRate * 100) . '%) — مشكلة', 'positive' => false];
            }
        } else {
            $points[] = ['icon' => 'ℹ️', 'text' => 'لا توجد خطط تقسيط لتقييم الالتزام', 'positive' => null];
        }

        // 2. Volume of business
        $confirmedOrders = $orders->whereNotIn('status', ['cancelled', 'draft']);
        $orderCount      = $confirmedOrders->count();
        $invoicedTotal   = $confirmedOrders->sum('total');

        if ($invoicedTotal >= 50000) {
            $score += 25;
            $points[] = ['icon' => '💰', 'text' => 'حجم تعامل ممتاز: ' . number_format($invoicedTotal, 0) . ' ريال', 'positive' => true];
        } elseif ($invoicedTotal >= 10000) {
            $score += 15;
            $points[] = ['icon' => '💵', 'text' => 'حجم تعامل جيد: ' . number_format($invoicedTotal, 0) . ' ريال', 'positive' => true];
        } elseif ($invoicedTotal > 0) {
            $score += 5;
            $points[] = ['icon' => '📋', 'text' => 'حجم تعامل محدود: ' . number_format($invoicedTotal, 0) . ' ريال', 'positive' => null];
        }

        // 3. Collections behavior
        $completedCollections = $collections->where('status', 'completed')->count();
        if ($completedCollections >= 5) {
            $score += 20;
            $points[] = ['icon' => '✅', 'text' => 'سجل تحصيل ممتاز (' . $completedCollections . ' تحصيل)', 'positive' => true];
        } elseif ($completedCollections >= 2) {
            $score += 10;
            $points[] = ['icon' => '🟡', 'text' => 'سجل تحصيل متوسط (' . $completedCollections . ' تحصيل)', 'positive' => true];
        }

        // 4. Outstanding debt vs credit limit
        $creditLimit = (float) $customer->credit_limit;
        if ($creditLimit > 0) {
            $utilizationRate = $currentBalance / $creditLimit;
            if ($utilizationRate > 1.0) {
                $score -= 20;
                $points[] = ['icon' => '🔴', 'text' => 'تجاوز الحد الائتماني بنسبة ' . round(($utilizationRate - 1) * 100) . '%', 'positive' => false];
            } elseif ($utilizationRate > 0.8) {
                $score -= 5;
                $points[] = ['icon' => '🟠', 'text' => 'قريب من الحد الائتماني (' . round($utilizationRate * 100) . '% مستخدم)', 'positive' => false];
            } elseif ($utilizationRate <= 0.5) {
                $score += 15;
                $points[] = ['icon' => '✅', 'text' => 'استخدام معقول للحد الائتماني (' . round($utilizationRate * 100) . '%)', 'positive' => true];
            }
        }

        // 5. Returns ratio
        $returnsTotal = $confirmedOrders->sum('total');
        $returnsAmount = $collections->where('status', 'completed')->count(); // not used directly
        $returnOrders = $orders->where('status', 'cancelled')->count();
        if ($orderCount > 0 && $returnOrders / max($orderCount, 1) > 0.3) {
            $score -= 10;
            $points[] = ['icon' => '🔴', 'text' => 'نسبة إلغاء/مرتجع مرتفعة', 'positive' => false];
        }

        // ── Recommendation ─────────────────────────────────────────
        $score = max(0, min(100, $score));

        if ($score >= 70) {
            $recommendation = 'يُنصح برفع الحد الائتماني';
            $recommendationColor = 'green';
            $recommendationIcon  = '⬆️';
        } elseif ($score >= 50) {
            $recommendation = 'الحد الائتماني الحالي مناسب';
            $recommendationColor = 'blue';
            $recommendationIcon  = '✔️';
        } elseif ($score >= 30) {
            $recommendation = 'يُفضل مراقبة العميل قبل رفع الحد';
            $recommendationColor = 'amber';
            $recommendationIcon  = '⚠️';
        } else {
            $recommendation = 'لا يُنصح برفع الحد الائتماني حالياً';
            $recommendationColor = 'red';
            $recommendationIcon  = '🚫';
        }

        // Suggested credit limit
        $suggestedLimit = $creditLimit;
        if ($score >= 70 && $invoicedTotal > 0) {
            $suggestedLimit = round(max($creditLimit * 1.3, $currentBalance * 1.5) / 1000) * 1000;
        }

        return [
            'score'               => $score,
            'points'              => $points,
            'recommendation'      => $recommendation,
            'recommendationColor' => $recommendationColor,
            'recommendationIcon'  => $recommendationIcon,
            'suggestedLimit'      => $suggestedLimit,
            'overdueCount'        => $overdueCount,
            'totalEntries'        => $totalEntries,
            'orderCount'          => $orderCount,
        ];
    }
}
