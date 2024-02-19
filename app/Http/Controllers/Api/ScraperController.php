<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScraperRequest;
use App\Http\Resources\ScrapeRecordResource;
use App\Models\ScrapeRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScraperController extends Controller
{
    /**
     * Handles an incoming request to initiate the website scraping process.
     *
     * @param StoreScraperRequest $request The validated request object containing the scrape parameters.
     * @return JsonResponse A formatted JSON response containing the scrape record and HTTP status code.
     */
    public function __invoke(StoreScraperRequest $request): JsonResponse
    {
        // Initiate the scraping process
        $response = Http::post(
            route('api.scrape-records.store'),
            $request->validated()
        );

        // Retrieves the 'data' part of the JSON response
        $data = $response->json()['data'];

        // Retrieves the first scrape record that matches the given UUID and updates it
        $scrapeRecord = ScrapeRecord::where('uuid', $data['uuid'])->first();
        $scrapeRecord->update(['status_code' => $response->status()]);

        // Returns the scrape result and status code
        return (new ScrapeRecordResource($scrapeRecord))
            ->response()
            ->setStatusCode($response->status());
    }
}
