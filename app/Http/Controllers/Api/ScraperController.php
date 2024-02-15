<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScraperRequest;
use App\Http\Resources\ScrapeRecordResource;
use App\Models\ScrapeRecord;
use App\Services\ScraperService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScraperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        // Get all scrape records
        $scrapeRecords = ScrapeRecord::paginate(20);

        // Return paginated scrape records as a JSON response
        return ScrapeRecordResource::collection($scrapeRecords);
    }

    /**
     * Store a new scraped item based on the provided URLs and extraction rules.
     *
     * @param StoreScraperRequest $request
     * @param ScraperService $service
     * @return JsonResponse|ScrapeRecordResource
     */
    public function store(
        StoreScraperRequest $request,
        ScraperService $service
    ): JsonResponse|ScrapeRecordResource {
        // Retrieve the validated request data
        $validated = $request->validated();

        // Set default values for the arguments
        $defaults = [
            'urls' => [],
            'extract_rules' => null,
            'async' => false,
        ];

        $args = array_merge($defaults, $validated);

        // Converts single URL string into an array if necessary
        if (is_string($args['urls'])) {
            $args['urls'] = [$args['urls']];
        }

        // Set 'async' to false if it's not set in the request
        // Determines if the scraping should be performed asynchronously
        $args['async'] = $args['async']
            ? filter_var($args['async'], FILTER_VALIDATE_BOOLEAN)
            : false;

        try {
            // Save the scrape record and obtain the formatted result
            $scrapeRecord = $service->saveScrapeRecord(
                $args['urls'],
                $args['extract_rules'],
                $args['async']
            );

            // Return the formatted scrape record as a JSON response
            return new ScrapeRecordResource($scrapeRecord);
        } catch (Exception $e) {
            // Handle exceptions by returning a generic error message
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
     * @param ScrapeRecord $scrapeRecord The scrape record.
     * @return JsonResponse|ScrapeRecordResource
     */
    public function show(
        ScrapeRecord $scrapeRecord
    ): JsonResponse|ScrapeRecordResource {
        // Check if the scrape record exist
        if (!$scrapeRecord) {
            return response()->json(
                [
                    'message' => 'Scrape record does not exist',
                ],
                404
            );
        }

        // Return the formatted scrape record as a JSON response
        return new ScrapeRecordResource($scrapeRecord);
    }

    /**
     * Remove the specified scrape record resource.
     *
     * @param ScrapeRecord $scrapeRecord The scrape record.
     * @return JsonResponse
     */
    public function destroy(ScrapeRecord $scrapeRecord): JsonResponse
    {
        // Delete the scrape record
        $deleted = $scrapeRecord->delete();

        // Check if the scrape record was actually deleted
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
