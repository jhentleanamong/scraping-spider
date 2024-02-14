<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Sushi\Sushi;

class ScrapeRecord extends Model
{
    use Sushi;

    protected $casts = [
        'id' => 'string',
        'urls' => 'json',
        'extract_rules' => 'json',
        'results' => 'json',
    ];

    public function getRows(): array
    {
        $apiKey = auth()->user()->api_key;
        $response = Http::withToken($apiKey)
            ->asJson()
            ->acceptJson()
            ->get(route('api.jobs.index'));

        return $this->formatRecords($response->json());
    }

    public function formatRecords(array $records): array
    {
        $formattedRecords = [];

        foreach ($records as $record) {
            $formattedRecords[] = [
                'id' => $record['id'],
                'details_url' => $record['details_url'],
                'urls' => json_encode($record['urls']),
                'extract_rules' => json_encode($record['extract_rules']),
                'results' => json_encode($record['results']),
                'status' => $record['status'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
            ];
        }

        return $formattedRecords;
    }
}
