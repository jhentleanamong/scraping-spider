<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class RequestBuilder extends Page
{
    protected static ?string $title = 'API Request Builder';

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'API Request Builder';

    protected static string $view = 'filament.app.pages.request-builder';
}
