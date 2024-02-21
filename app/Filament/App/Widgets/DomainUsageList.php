<?php

namespace App\Filament\App\Widgets;

use App\Models\ScrapeRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class DomainUsageList extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Usage By Domain';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ScrapeRecord::query()
                    ->select('url')
                    ->selectRaw('COUNT(*) as number_of_requests')
                    ->selectRaw(
                        'SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as successful'
                    )
                    ->selectRaw(
                        'SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed'
                    )
                    ->groupBy('url')
                    ->limit(50)
            )
            ->columns([
                TextColumn::make('url')
                    ->label('Domain')
                    ->sortable(),
                TextColumn::make('successful')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('failed')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('number_of_requests')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('number_of_requests', 'desc')
            ->paginated(['10', '25', '50']);
    }

    public function getTableRecordKey(Model $record): string
    {
        return 'url';
    }
}
