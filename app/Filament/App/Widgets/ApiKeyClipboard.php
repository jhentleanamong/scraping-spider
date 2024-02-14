<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;

class ApiKeyClipboard extends Widget
{
    protected static bool $isLazy = false;

    protected static string $view = 'filament.app.widgets.api-key-clipboard';
}
