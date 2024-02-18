<?php

namespace App\Services;

use App\Jobs\ScrapeWebsite;
use App\Models\ScrapeRecord;
use App\Models\User;
use App\Spiders\UniversalSpider;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class ScraperService
{
    /**
     * Extract data from a specified URL using extraction rules.
     *
     * @param array $urls An array of URLs to scrape data from.
     * @param mixed $rules The extraction rules to apply.
     * @return array An array containing the extracted data.
     */
    public function extract(array $urls, mixed $rules): array
    {
        return Roach::collectSpider(
            UniversalSpider::class,
            new Overrides(startUrls: $urls),
            context: ['extract_rules' => $rules]
        );
    }

    /**
     * Create and store a scrape record in Redis based on provided URLs and rules.
     *
     * @param User $user The user model.
     * @param array $urls  The URLs to be scraped.
     * @param mixed $rules  The extraction rules for scraping.
     * @param bool $async Flag to determine if scraping should be processed asynchronously.
     * @return ScrapeRecord  The scrape record data.
     */
    public function saveScrapeRecord(
        User $user,
        array $urls,
        mixed $rules,
        bool $async = false
    ): ScrapeRecord {
        // Store initial scrape record data
        $scrapeRecord = $user->scrapeRecords()->create([
            'urls' => $urls,
            'extract_rules' => json_decode($rules, true),
            'results' => '',
            'status' => 'pending',
        ]);

        if ($async) {
            // Dispatch the job to scrape the websites
            ScrapeWebsite::dispatch($scrapeRecord, $urls, $rules);

            // Set the status to 'in-progress'
            $scrapeRecord->update(['status' => 'in-progress']);

            // Return the scrape record data
            return $scrapeRecord;
        }

        // Extract data from the provided URLs and rules
        $items = $this->extract($urls, $rules);

        // Transform and store the extracted data and update the status
        $results = collect($items)->map(function ($item) {
            return $item->all();
        });

        // Update scrape record with the result and status
        $scrapeRecord->update(['results' => $results, 'status' => 'completed']);

        // Return the scrape record data
        return $scrapeRecord;
    }
}
