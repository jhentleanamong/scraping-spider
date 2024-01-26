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

        $id = Str::orderedUuid();
        $key = 'scrape_record:' . $id;

        Redis::hmset($key, [
            'id' => $id,
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

            return $service->formatRecord(Redis::hgetall($key));
        } catch (\Exception $e) {
            // Update Redis with error status
            Redis::hmset($key, [
                'status' => 'failed',
                'updated_at' => now(),
            ]);

            return $service->formatRecord(Redis::hgetall($key));
        }
    }

    /**
     * Display the specified scrape record resource.
     *
     * @param string $id The unique identifier for the scraping record.
     * @param ScraperService $service The service responsible for formatting scraping records.
     * @return array The formatted scraping record.
     */
    public function show(string $id, ScraperService $service): array
    {
        $key = 'scrape_record:' . $id;
        return $service->formatRecord(Redis::hgetall($key));
    }
}
