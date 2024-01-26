<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Use the ScraperService to extract data
        $items = $service->extract(
            $validated['url'],
            $validated['extract_rules']
        );

        // Transform the extracted items and return the first one
        return collect($items)
            ->map(function ($item) {
                return $item->all();
            })
            ->first();
    }
}
