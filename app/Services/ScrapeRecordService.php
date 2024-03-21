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
     * @param array $options An array of elements that make up scrape record.
     * @return ScrapeRecord  The scrape record data.
     */
    public function create(
        User $user,
        string $url,
        array $options = []
    ): ScrapeRecord {
        // Set default values for the arguments
        $defaults = [
            'extract_rules' => null,
            'screenshot' => false,
            'async' => false,
        ];

        // Merge validated data with defaults, prioritizing validated values
        $options = array_merge($defaults, $options);

        // Store initial scrape record data
        $scrapeRecord = $user->scrapeRecords()->create([
            'url' => $url,
            'options' => [
                'extract_rules' => json_decode($options['extract_rules'], true),
                'screenshot' => $options['screenshot'],
            ],
            'result' => '',
            'status' => 'pending',
        ]);

        // If async flag is true, dispatch the job to scrape the URL in the background.
        if ($options['async']) {
            // Dispatch the job to scrape the websites
            ScrapeWebsite::dispatch($scrapeRecord, $url, $options);

            // Set the status to 'in-progress'
            $scrapeRecord->update(['status' => 'in-progress']);

            // Return the scrape record data
            return $scrapeRecord;
        }

        // Extract data from the provided URLs and rules
        $extractedData = (new ScraperService())->extractData(
            $url,
            $options['extract_rules']
        );

        // Format the extracted data
        $extractedData = collect($extractedData)->map(function ($item) {
            return $item->all();
        });

        // Store the extracted data
        $result = [
            'body' => $extractedData,
        ];

        // If the screenshot option is enabled, take a screenshot of the URL.
        if ($options['screenshot']) {
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
