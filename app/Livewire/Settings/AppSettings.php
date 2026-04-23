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

    #[Validate('required|string|max:20')]
    public string $currency = '';

    #[Validate('nullable|string|max:50')]
    public string $tax_number = '';

    #[Validate('nullable|string|max:50')]
    public string $commercial_register = '';

    #[Validate('nullable|image|max:2048')]
    public $logo = null;

    public ?string $currentLogo = null;

    public bool $saved = false;

    public function mount(): void
    {
        $this->app_name           = Setting::get('app_name', '');
        $this->currency           = Setting::get('currency', '');
        $this->tax_number         = Setting::get('tax_number', '');
        $this->commercial_register = Setting::get('commercial_register', '');
        $this->currentLogo        = Setting::get('logo');
    }

    public function save(): void
    {
        $this->validate();

        Setting::set('app_name', $this->app_name);
        Setting::set('currency', $this->currency);
        Setting::set('tax_number', $this->tax_number ?? '');
        Setting::set('commercial_register', $this->commercial_register ?? '');

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            Setting::set('logo', $path);
            $this->currentLogo = $path;
            $this->logo = null;
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.settings.settings');
    }
}
