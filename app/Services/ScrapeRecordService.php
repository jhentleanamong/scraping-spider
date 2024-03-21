<?php

namespace App\Services;

use App\Jobs\ScrapeWebsite;
use App\Models\ScrapeRecord;
use App\Models\User;

class ScrapeRecordService
{
    /**
     * Create and store a scrape record based on provided URL and rules.
     *
     * @param User $user The user model.
     * @param string $url  The URL to be scraped.
     * @param mixed $rules  The extraction rules for scraping.
     * @param bool $async Flag to determine if scraping should be processed asynchronously.
     * @return ScrapeRecord  The scrape record data.
     */
    public function create(
        User $user,
        string $url,
        mixed $rules = null,
        bool $screenshot = false,
        bool $async = false
    ): ScrapeRecord {
        // Store initial scrape record data
        $scrapeRecord = $user->scrapeRecords()->create([
            'url' => $url,
            'extract_rules' => json_decode($rules, true),
            'result' => '',
            'status' => 'pending',
        ]);

        if ($async) {
            // Dispatch the job to scrape the websites
            ScrapeWebsite::dispatch($scrapeRecord, $url, $rules, $screenshot);

            // Set the status to 'in-progress'
            $scrapeRecord->update(['status' => 'in-progress']);

            // Return the scrape record data
            return $scrapeRecord;
        }

        // Extract data from the provided URLs and rules
        $items = (new ScraperService())->extractData($url, $rules);

        // Store the extracted data
        $result = [
            'body' => collect($items)->map(function ($item) {
                return $item->all();
            }),
        ];

        if ($screenshot) {
            $result['screenshot'] = (new ScraperService())->takeScreenshot(
                $url
            );
        }

        // Update scrape record with the result and status
        $scrapeRecord->update(['result' => $result, 'status' => 'completed']);

        // Return the scrape record data
        return $scrapeRecord;
    }
}
