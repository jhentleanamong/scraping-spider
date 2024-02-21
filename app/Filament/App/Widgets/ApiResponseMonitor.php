<?php

namespace App\Filament\App\Widgets;

use App\Models\ScrapeRecord;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApiResponseMonitor extends ChartWidget
{
    protected static ?string $heading = 'API Response Monitor';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'week';

    protected function getData(): array
    {
        $user = Auth::user();

        $startDate = [
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
        ];

        $endDate = now();

        $totalResponse = $this->getTrendData(
            $user->id,
            $startDate[$this->filter],
            $endDate
        );

        $successfulResponse = $this->getTrendData(
            $user->id,
            $startDate[$this->filter],
            $endDate,
            [200, 299]
        );

        $failedResponse = $this->getTrendData(
            $user->id,
            $startDate[$this->filter],
            $endDate,
            [400, 599]
        );

        return [
            'datasets' => [
                [
                    'label' => 'Successful',
                    'data' => $successfulResponse->map(
                        fn(TrendValue $value) => $value->aggregate
                    ),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(22, 163, 74)',
                ],
                [
                    'label' => 'Failed',
                    'data' => $failedResponse->map(
                        fn(TrendValue $value) => $value->aggregate
                    ),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgb(220, 38, 38)',
                ],
            ],
            'labels' => $totalResponse->map(
                fn(TrendValue $value) => Carbon::createFromFormat(
                    'Y-m-d',
                    $value->date
                )->format('M d')
            ),
        ];
    }

    protected function getTrendData(
        $userId,
        $startDate,
        $endDate,
        $statusCodeRange = null
    ) {
        return Trend::query(
            ScrapeRecord::where('user_id', $userId)->when(
                $statusCodeRange,
                function (Builder $query, mixed $statusCodeRange) {
                    $query->whereBetween('status_code', $statusCodeRange);
                }
            )
        )
            ->between(start: $startDate, end: $endDate)
            ->perDay()
            ->count();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last Week',
            'month' => 'Last Month',
        ];
    }
}
