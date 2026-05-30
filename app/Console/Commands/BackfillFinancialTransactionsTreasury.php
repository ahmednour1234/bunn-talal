<?php

namespace App\Console\Commands;

use App\Models\FinancialTransaction;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillFinancialTransactionsTreasury extends Command
{
    protected $signature = 'fix:financial-transactions-treasury';

    protected $description = 'إنشاء حركات خزنة للمصروفات والإيرادات القديمة التي لا تملك حركة خزنة مرتبطة';

    public function handle(): int
    {
        $transactions = FinancialTransaction::whereNotNull('treasury_id')
            ->whereNull('treasury_transaction_id')
            ->withTrashed()
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('لا توجد معاملات تحتاج إلى إصلاح.');
            return self::SUCCESS;
        }

        $this->info("عدد المعاملات المطلوب إصلاحها: {$transactions->count()}");
        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        $fixed = 0;
        $failed = 0;

        foreach ($transactions as $transaction) {
            try {
                DB::transaction(function () use ($transaction) {
                    $treasuryType = $transaction->type === 'expense' ? 'withdrawal' : 'deposit';

                    $treasuryTx = TreasuryTransaction::create([
                        'treasury_id'      => $transaction->treasury_id,
                        'type'             => $treasuryType,
                        'amount'           => $transaction->amount,
                        'description'      => $transaction->description,
                        'date'             => $transaction->date,
                        'reference_number' => 'FT-' . $transaction->id,
                        'admin_id'         => $transaction->admin_id,
                    ]);

                    $treasury = Treasury::findOrFail($transaction->treasury_id);
                    if ($treasuryType === 'deposit') {
                        $treasury->increment('balance', $transaction->amount);
                    } else {
                        $treasury->decrement('balance', $transaction->amount);
                    }

                    $transaction->withoutEvents(function () use ($transaction, $treasuryTx) {
                        $transaction->treasury_transaction_id = $treasuryTx->id;
                        $transaction->saveQuietly();
                    });
                });

                $fixed++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("فشل معالجة المعاملة #{$transaction->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("تم إصلاح: {$fixed} | فشل: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
