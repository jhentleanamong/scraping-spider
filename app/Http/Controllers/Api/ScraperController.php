<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScraperService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ScraperController extends Controller
{
    /**
     * Store a new scraped item based on the provided URLs and extraction rules.
     *
     * @param Request $request
     * @param ScraperService $service
     * @return JsonResponse
     */
    public function store(
        Request $request,
        ScraperService $service
    ): JsonResponse {
        // Validate the incoming request data
        $validated = $request->validate([
            'urls' => ['required', 'array'],
            'urls.*' => ['string', 'url'],
            'extract_rules' => ['nullable', 'string'],
        ]);

        if (is_string($validated['urls'])) {
            $validated['urls'] = [$validated['urls']];
        }

        try {
            // Save the scrape record and obtain the formatted result
            $scrapeRecord = $service->saveScrapeRecord(
                $validated['urls'],
                $validated['extract_rules']
            );

            // Return the formatted scrape record as a JSON response
            return response()->json($scrapeRecord, 200);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' =>
                        'An unexpected error occurred. Please try again later.',
                ],
                500
            );
        }
    }

    /**
     * Display the specified scrape record resource.
     *
     * @param string $id The unique identifier for the scraping record.
     * @param ScraperService $service The service responsible for formatting scraping records.
     * @return JsonResponse The formatted scraping record.
     */
    public function show(string $id, ScraperService $service): JsonResponse
    {
        $key = 'scrape_record:' . $id;
        $record = Redis::hgetall($key);

        // Check if the record exist
        if (!$record) {
            return response()->json(
                [
                    'message' => 'Scrape record does not exist',
                ],
                404
            );
        }

        return response()->json(
            $service->formatRecord(Redis::hgetall($key)),
            200
        );
    }

    /**
     * Remove the specified scrape record resource.
     *
     * @param string $id The unique identifier for the scraping record.
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $key = 'scrape_record:' . $id;

        // Delete the entire hash
        $deleted = Redis::del($key);

        // Check if the hash was actually deleted
        if (!$deleted) {
            return response()->json(
                [
                    'message' => 'Scrape record does not exist',
                ],
                404
            );
        }

        return response()->json(
            [
                'message' => 'Scrape record successfully deleted',
            ],
            200
        );
    }
}
