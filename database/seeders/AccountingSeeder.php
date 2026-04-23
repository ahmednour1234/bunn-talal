<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Admin;
use App\Models\FinancialTransaction;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();
        $adminId = $admin?->id ?? 1;

        // Accounts
        $accounts = [
            ['name' => 'مصروفات تشغيلية', 'account_number' => 'ACC-001', 'visible_to_delegate' => false, 'is_active' => true],
            ['name' => 'مصروفات نقل', 'account_number' => 'ACC-002', 'visible_to_delegate' => true, 'is_active' => true],
            ['name' => 'مصروفات رواتب', 'account_number' => 'ACC-003', 'visible_to_delegate' => false, 'is_active' => true],
            ['name' => 'مصروفات صيانة', 'account_number' => 'ACC-004', 'visible_to_delegate' => false, 'is_active' => true],
            ['name' => 'إيرادات مبيعات', 'account_number' => 'ACC-005', 'visible_to_delegate' => true, 'is_active' => true],
            ['name' => 'إيرادات خدمات', 'account_number' => 'ACC-006', 'visible_to_delegate' => true, 'is_active' => true],
            ['name' => 'مصروفات متنوعة', 'account_number' => 'ACC-007', 'visible_to_delegate' => false, 'is_active' => true],
            ['name' => 'إيرادات أخرى', 'account_number' => 'ACC-008', 'visible_to_delegate' => false, 'is_active' => true],
        ];

        foreach ($accounts as $data) {
            Account::firstOrCreate(['account_number' => $data['account_number']], $data);
        }

        // Treasuries
        $treasuries = [
            ['name' => 'الخزنة الرئيسية', 'balance' => 50000.00, 'is_active' => true],
            ['name' => 'خزنة الفرع الأول', 'balance' => 15000.00, 'is_active' => true],
            ['name' => 'خزنة الفرع الثاني', 'balance' => 8000.00, 'is_active' => true],
            ['name' => 'خزنة المناديب', 'balance' => 5000.00, 'is_active' => true],
        ];

        foreach ($treasuries as $data) {
            Treasury::firstOrCreate(['name' => $data['name']], $data);
        }

        $allTreasuries = Treasury::all();
        $allAccounts = Account::all();

        // Treasury Transactions
        $treasuryTransactions = [
            ['treasury_id' => $allTreasuries[0]->id, 'type' => 'deposit', 'amount' => 25000.00, 'description' => 'رأس مال تأسيسي', 'date' => '2026-01-01', 'reference_number' => 'TT-001', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[0]->id, 'type' => 'deposit', 'amount' => 25000.00, 'description' => 'إيداع إضافي', 'date' => '2026-01-15', 'reference_number' => 'TT-002', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[1]->id, 'type' => 'deposit', 'amount' => 15000.00, 'description' => 'تمويل فرع', 'date' => '2026-01-10', 'reference_number' => 'TT-003', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[0]->id, 'type' => 'withdrawal', 'amount' => 5000.00, 'description' => 'سحب لمصاريف يومية', 'date' => '2026-02-01', 'reference_number' => 'TT-004', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[2]->id, 'type' => 'deposit', 'amount' => 8000.00, 'description' => 'تمويل فرع ثاني', 'date' => '2026-02-05', 'reference_number' => 'TT-005', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[3]->id, 'type' => 'deposit', 'amount' => 5000.00, 'description' => 'تمويل خزنة مناديب', 'date' => '2026-02-10', 'reference_number' => 'TT-006', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[1]->id, 'type' => 'withdrawal', 'amount' => 3000.00, 'description' => 'سحب لتغطية مصروفات', 'date' => '2026-03-01', 'reference_number' => 'TT-007', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[0]->id, 'type' => 'deposit', 'amount' => 10000.00, 'description' => 'إيداع إيرادات شهر مارس', 'date' => '2026-03-15', 'reference_number' => 'TT-008', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[2]->id, 'type' => 'withdrawal', 'amount' => 2000.00, 'description' => 'سحب مصروفات', 'date' => '2026-03-20', 'reference_number' => 'TT-009', 'admin_id' => $adminId],
            ['treasury_id' => $allTreasuries[0]->id, 'type' => 'withdrawal', 'amount' => 7000.00, 'description' => 'سحب رواتب', 'date' => '2026-04-01', 'reference_number' => 'TT-010', 'admin_id' => $adminId],
        ];

        foreach ($treasuryTransactions as $data) {
            TreasuryTransaction::firstOrCreate(['reference_number' => $data['reference_number']], $data);
        }

        // Financial Transactions
        $financialTransactions = [
            ['type' => 'expense', 'account_id' => $allAccounts[0]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 3500.00, 'description' => 'إيجار مكتب شهر يناير', 'date' => '2026-01-05', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[1]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 2000.00, 'description' => 'وقود سيارات', 'date' => '2026-01-10', 'admin_id' => $adminId],
            ['type' => 'revenue', 'account_id' => $allAccounts[4]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 15000.00, 'description' => 'مبيعات شهر يناير', 'date' => '2026-01-20', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[2]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 8000.00, 'description' => 'رواتب موظفين يناير', 'date' => '2026-01-28', 'admin_id' => $adminId],
            ['type' => 'revenue', 'account_id' => $allAccounts[5]->id, 'treasury_id' => $allTreasuries[1]->id, 'amount' => 5000.00, 'description' => 'خدمات توصيل', 'date' => '2026-02-01', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[3]->id, 'treasury_id' => $allTreasuries[1]->id, 'amount' => 1500.00, 'description' => 'صيانة مركبة', 'date' => '2026-02-10', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[0]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 3500.00, 'description' => 'إيجار مكتب شهر فبراير', 'date' => '2026-02-05', 'admin_id' => $adminId],
            ['type' => 'revenue', 'account_id' => $allAccounts[4]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 18000.00, 'description' => 'مبيعات شهر فبراير', 'date' => '2026-02-20', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[2]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 8000.00, 'description' => 'رواتب موظفين فبراير', 'date' => '2026-02-28', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[1]->id, 'treasury_id' => $allTreasuries[2]->id, 'amount' => 1200.00, 'description' => 'وقود مناديب', 'date' => '2026-03-01', 'admin_id' => $adminId],
            ['type' => 'revenue', 'account_id' => $allAccounts[4]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 20000.00, 'description' => 'مبيعات شهر مارس', 'date' => '2026-03-15', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[6]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 800.00, 'description' => 'مصروفات متفرقة', 'date' => '2026-03-20', 'admin_id' => $adminId],
            ['type' => 'revenue', 'account_id' => $allAccounts[7]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 3000.00, 'description' => 'إيرادات إضافية', 'date' => '2026-04-01', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[2]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 8500.00, 'description' => 'رواتب موظفين مارس', 'date' => '2026-03-28', 'admin_id' => $adminId],
            ['type' => 'expense', 'account_id' => $allAccounts[0]->id, 'treasury_id' => $allTreasuries[0]->id, 'amount' => 3500.00, 'description' => 'إيجار مكتب شهر مارس', 'date' => '2026-03-05', 'admin_id' => $adminId],
        ];

        foreach ($financialTransactions as $data) {
            FinancialTransaction::create($data);
        }
    }
}
