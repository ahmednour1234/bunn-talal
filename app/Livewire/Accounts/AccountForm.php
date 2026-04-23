<?php

namespace App\Livewire\Accounts;

use App\Services\AccountService;
use Livewire\Component;

class AccountForm extends Component
{
    public ?int $accountId = null;
    public string $name = '';
    public string $account_number = '';
    public bool $visible_to_delegate = false;
    public bool $is_active = true;

    public function mount(AccountService $accountService, ?int $id = null)
    {
        if ($id) {
            $this->accountId = $id;
            $account = $accountService->getAccountById($id);
            $this->name = $account->name;
            $this->account_number = $account->account_number;
            $this->visible_to_delegate = $account->visible_to_delegate;
            $this->is_active = $account->is_active;
        }
    }

    protected function rules(): array
    {
        $unique = $this->accountId ? ',' . $this->accountId : '';
        return [
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:accounts,account_number' . $unique,
            'visible_to_delegate' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم الحساب مطلوب',
            'account_number.required' => 'رقم الحساب مطلوب',
            'account_number.unique' => 'رقم الحساب مستخدم بالفعل',
        ];
    }

    public function save(AccountService $accountService)
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'account_number' => $this->account_number,
            'visible_to_delegate' => $this->visible_to_delegate,
            'is_active' => $this->is_active,
        ];

        if ($this->accountId) {
            $accountService->updateAccount($this->accountId, $data);
            session()->flash('success', 'تم تحديث الحساب بنجاح');
        } else {
            $accountService->createAccount($data);
            session()->flash('success', 'تم إضافة الحساب بنجاح');
        }

        return redirect()->route('accounts.index');
    }

    public function render()
    {
        return view('livewire.accounts.account-form');
    }
}
