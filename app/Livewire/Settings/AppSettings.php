<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppSettings extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:100')]
    public string $app_name = '';

    #[Validate('required|string|max:50')]
    public string $currency = '';

    #[Validate('nullable|string|max:50')]
    public string $tax_number = '';

    #[Validate('nullable|string|max:50')]
    public string $commercial_register = '';

    #[Validate('nullable|image|max:2048')]
    public $logo = null;

    public ?string $currentLogo = null;

    public bool $saved = false;
    public bool $logoSaved = false;

    public function mount(): void
    {
        $this->app_name            = Setting::get('app_name', '');
        $this->currency            = Setting::get('currency', '');
        $this->tax_number          = Setting::get('tax_number', '');
        $this->commercial_register = Setting::get('commercial_register', '');
        $this->currentLogo         = Setting::get('logo');
    }

    public function save(): void
    {
        $this->validateOnly('app_name');
        $this->validateOnly('currency');
        $this->validateOnly('tax_number');
        $this->validateOnly('commercial_register');

        Setting::set('app_name', $this->app_name);
        Setting::set('currency', $this->currency);
        Setting::set('tax_number', $this->tax_number ?? '');
        Setting::set('commercial_register', $this->commercial_register ?? '');

        $this->saved = true;
    }

    public function uploadLogo(): void
    {
        $this->validateOnly('logo');

        if ($this->logo) {
            $ext  = $this->logo->getClientOriginalExtension();
            $name = 'logo_' . time() . '.' . $ext;
            $path = $this->logo->storeAs('logos', $name, 'public');

            if ($path) {
                Setting::set('logo', $path);
                $this->currentLogo = $path;
                $this->logo        = null;
                $this->logoSaved   = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.settings.settings');
    }
}
