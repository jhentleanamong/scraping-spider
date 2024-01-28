<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'super.administrator@example.net',
            'password' => 'password',
            'remember' => true,
        ]);
    }
}
