<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScraperRequest;
use App\Http\Resources\ScrapeRecordResource;
use App\Models\ScrapeRecord;
use App\Models\User;
use App\Services\ScrapeRecordService;
use App\Services\ScraperService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScrapeRecordController extends Controller
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
     * Store a new scraped item based on the provided URL and extraction rules.
     *
     * @param StoreScraperRequest $request
     * @param ScrapeRecordService $service
     * @return JsonResponse|ScrapeRecordResource
     */
    public function store(
        StoreScraperRequest $request,
        ScrapeRecordService $service
    ): JsonResponse|ScrapeRecordResource {
        // Retrieve the validated request data
        $validated = $request->validated();

        // Set default values for the arguments
        $defaults = [
            'url' => '',
            'extract_rules' => null,
            'screenshot' => false,
            'async' => false,
        ];

        // Merge validated data with defaults, prioritizing validated values
        $args = array_merge($defaults, $validated);

        // Generate an API key hash for user verification
        $apiKeyHash = hash_hmac(
            'sha256',
            $validated['api_key'],
            config('app.key')
        );

        // Look up the user by API key hash
        $user = User::where('api_key_hash', $apiKeyHash)->first();

        try {
            // Save the scrape record and obtain the formatted result
            $scrapeRecord = $service->create($user, $args['url'], $args);

            // Return the formatted scrape record as a JSON response
            return new ScrapeRecordResource($scrapeRecord);
        } catch (Exception $error) {
            // Handle exceptions by returning a generic error message
            return response()->json(
                [
                    'message' => $error->getMessage(),
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
