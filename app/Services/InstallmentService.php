<?php

namespace App\Services;

use App\Models\InstallmentEntry;
use App\Models\InstallmentPlan;
use App\Models\SaleOrder;
use App\Models\Treasury;
use App\Repositories\Contracts\InstallmentPlanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentService
{
    public function __construct(
        protected InstallmentPlanRepositoryInterface $planRepository
    ) {}

    // ── Read ──────────────────────────────────────────────────────

    public function getById(int $id): InstallmentPlan
    {
        return $this->planRepository->getById($id);
    }

    public function paginateWithFilters(
        int $perPage,
        ?string $search,
        ?string $status,
        ?string $partyType,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo
    ) {
        return $this->planRepository->paginateWithFilters(
            $perPage, $search, $status, $partyType, $branchId, $dateFrom, $dateTo
        );
    }

    public function getSummaryStats(): array
    {
        $active    = InstallmentPlan::where('status', 'active');
        $entries   = InstallmentEntry::join('installment_plans', 'installment_plans.id', '=', 'installment_entries.installment_plan_id')
                        ->where('installment_plans.status', 'active');

        $totalAmount    = (clone $active)->sum('total_amount');
        $totalPaid      = (clone $active)->sum('down_payment');
        // add entries paid_amount
        $entriesPaid    = InstallmentEntry::whereHas('plan', fn($q) => $q->where('status', 'active'))->sum('paid_amount');
        $totalPaid      = (float) $totalPaid + (float) $entriesPaid;

        $overdueCount   = InstallmentEntry::where('status', 'overdue')->count();
        $pendingCount   = InstallmentEntry::whereIn('status', ['pending', 'partial'])->count();
        $activePlans    = (clone $active)->count();

        return [
            'active_plans'    => $activePlans,
            'total_amount'    => (float) $totalAmount,
            'total_paid'      => (float) $totalPaid,
            'outstanding'     => max(0, (float) $totalAmount - (float) $totalPaid),
            'overdue_entries' => (int) $overdueCount,
            'pending_entries' => (int) $pendingCount,
        ];
    }

    // ── Create plan + auto-generate entries ───────────────────────

    public function createPlan(array $data): InstallmentPlan
    {
        return DB::transaction(function () use ($data) {
            $totalAmount      = (float) $data['total_amount'];
            $downPayment      = (float) ($data['down_payment'] ?? 0);
            $remaining        = $totalAmount - $downPayment;
            $count            = (int) $data['installments_count'];
            $perInstallment   = $count > 0 ? round($remaining / $count, 2) : $remaining;

            $plan = $this->planRepository->create([
                'party_type'         => $data['party_type'],
                'customer_id'        => $data['customer_id'] ?? null,
                'supplier_id'        => $data['supplier_id'] ?? null,
                'reference_type'     => $data['reference_type'] ?? 'manual',
                'reference_id'       => $data['reference_id'] ?? null,
                'branch_id'          => $data['branch_id'],
                'admin_id'           => $data['admin_id'],
                'treasury_id'        => $data['treasury_id'] ?? null,
                'start_date'         => $data['start_date'],
                'total_amount'       => $totalAmount,
                'down_payment'       => $downPayment,
                'remaining_amount'   => $remaining,
                'installments_count' => $count,
                'installment_amount' => $perInstallment,
                'frequency'          => $data['frequency'] ?? 'monthly',
                'status'             => 'active',
                'notes'              => $data['notes'] ?? null,
            ]);

            // Auto-generate installment entries
            $this->generateEntries($plan);

            return $plan->load('entries');
        });
    }

    protected function generateEntries(InstallmentPlan $plan): void
    {
        $startDate  = Carbon::parse($plan->start_date);
        $count      = $plan->installments_count;
        $amount     = (float) $plan->installment_amount;
        $remaining  = (float) $plan->remaining_amount;

        // Last installment gets any rounding remainder
        $lastAmount = round($remaining - ($amount * ($count - 1)), 2);

        for ($i = 1; $i <= $count; $i++) {
            $dueDate = match ($plan->frequency) {
                'weekly'   => $startDate->copy()->addWeeks($i),
                'biweekly' => $startDate->copy()->addWeeks($i * 2),
                'monthly'  => $startDate->copy()->addMonths($i),
                default    => $startDate->copy()->addMonths($i),
            };

            $entryAmount = ($i === $count) ? $lastAmount : $amount;

            $plan->entries()->create([
                'entry_number' => $i,
                'due_date'     => $dueDate->toDateString(),
                'amount'       => $entryAmount,
                'paid_amount'  => 0,
                'status'       => 'pending',
            ]);
        }
    }

    // ── Pay an installment entry ──────────────────────────────────

    public function payEntry(int $entryId, float $paidAmount, ?int $treasuryId, int $adminId, ?string $notes = null): InstallmentEntry
    {
        return DB::transaction(function () use ($entryId, $paidAmount, $treasuryId, $adminId, $notes) {
            $entry = InstallmentEntry::findOrFail($entryId);

            if ($entry->status === 'paid') {
                throw new \Exception('هذه الدفعة مسددة بالفعل');
            }

            $newPaid = (float) $entry->paid_amount + $paidAmount;

            if ($newPaid > (float) $entry->amount) {
                throw new \Exception('المبلغ المدفوع أكبر من المبلغ المطلوب');
            }

            $status = $newPaid >= (float) $entry->amount ? 'paid' : 'partial';

            $entry->update([
                'paid_amount' => $newPaid,
                'status'      => $status,
                'paid_at'     => now()->toDateString(),
                'treasury_id' => $treasuryId,
                'admin_id'    => $adminId,
                'notes'       => $notes,
            ]);

            // Update treasury balance
            if ($treasuryId) {
                $treasury = Treasury::findOrFail($treasuryId);
                // customer pays us → treasury increases; we pay supplier → treasury decreases
                if ($entry->plan->party_type === 'customer') {
                    $treasury->increment('balance', $paidAmount);
                } else {
                    $treasury->decrement('balance', $paidAmount);
                }
            }

            // Check if all entries are paid → mark plan complete
            $plan = $entry->plan;
            $allPaid = $plan->entries()->where('status', '!=', 'paid')->doesntExist();
            if ($allPaid) {
                $plan->update(['status' => 'completed']);
            }

            // If plan is linked to a sale order → sync its paid_amount from all plan entries
            if ($plan->reference_type === 'sale_order' && $plan->reference_id) {
                $order = SaleOrder::find($plan->reference_id);
                if ($order) {
                    // Sum all entry paid_amounts + down payment to get total paid via this plan
                    $totalPaidViaEntries = (float) $plan->entries()->sum('paid_amount') + (float) $plan->down_payment;
                    $newPaid = min(round($totalPaidViaEntries, 2), (float) $order->total);
                    $newStatus = $newPaid >= (float) $order->total ? 'paid' : 'partial_paid';
                    $order->update([
                        'paid_amount' => $newPaid,
                        'status'      => $newStatus,
                    ]);
                }
            }

            // Auto mark overdue entries on check
            $this->markOverdue($plan);

            return $entry->fresh();
        });
    }

    // ── Mark overdue ──────────────────────────────────────────────

    public function markOverdue(InstallmentPlan $plan): void
    {
        $plan->entries()
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    public function markAllOverdue(): void
    {
        InstallmentEntry::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    // ── Cancel plan ───────────────────────────────────────────────

    public function cancelPlan(int $id): void
    {
        DB::transaction(function () use ($id) {
            $plan = $this->planRepository->getById($id);
            if ($plan->status !== 'active') {
                throw new \Exception('لا يمكن إلغاء هذه الخطة');
            }
            $plan->update(['status' => 'cancelled']);
        });
    }
}
