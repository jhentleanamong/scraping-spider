<?php

namespace App\Filament\App\Widgets;

use App\Models\ScrapeRecord;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;

class RequestList extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'API Request List';

    public function table(Table $table): Table
    {
        return $table
            ->query(ScrapeRecord::query())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'pending' => 'gray',
                            'in-progress' => 'primary',
                            'completed' => 'success',
                        }
                    )
                    ->formatStateUsing(
                        fn(string $state): string => str($state)->headline()
                    )
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Request Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->icon('heroicon-o-magnifying-glass')
                    ->modalHeading('View Request Record')
                    ->modalContent(function (ScrapeRecord $record) {
                        return $this->getRecord($record);
                    })
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false),

                Action::make('delete')
                    ->modalHeading('Delete Record')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->action(function (ScrapeRecord $record) {
                        $this->delete($record);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRecord(ScrapeRecord $record): View
    {
        $apiKey = Auth::user()->api_key;
        $response = Http::withToken($apiKey)->get(
            route('api.jobs.show', $record->id)
        );

        return view('filament.app.pages.actions.record', compact('response'));
    }

    public function delete(ScrapeRecord $record): void
    {
        $response = Http::delete(route('api.jobs.destroy', $record->id));

        if ($response->failed()) {
            Notification::make()
                ->title('Oops, something went wrong')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Record deleted successfully')
            ->success()
            ->send();
    }
}
