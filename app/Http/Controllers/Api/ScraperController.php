<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ScraperController extends Controller
{
    /**
     * Store a new scraped item based on the provided URL and extraction rules.
     *
     * @param Request $request
     * @param ScraperService $service
     * @return array
     */
    public function store(Request $request, ScraperService $service): array
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'url' => ['required'],
            'extract_rules' => ['nullable', 'string'],
        ]);

        $uuid = Str::orderedUuid();
        $key = 'scrape_record:' . $uuid;

        Redis::hmset($key, [
            'id' => $uuid,
            'url' => $validated['url'],
            'extract_rules' => $validated['extract_rules'],
            'result' => '',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Use the ScraperService to extract data
            $items = $service->extract(
                $validated['url'],
                $validated['extract_rules']
            );

            $result = collect($items)
                ->map(function ($item) {
                    return $item->all();
                })
                ->first();

            // Update Redis with the result and status
            Redis::hmset($key, [
                'result' => json_encode($result),
                'status' => 'complete',
            ]);
        } catch (\Exception $e) {
            // Update Redis with error status
            Redis::hmset($key, [
                'status' => 'failed',
                'updated_at' => now(),
            ]);
        }

        // Transform the extracted items and return the first one
        return collect($items)
            ->map(function ($item) {
                return $item->all();
            })
            ->first();
    }
}
