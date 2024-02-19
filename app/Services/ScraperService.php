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
     * @param string $url A URL to scrape data from.
     * @param mixed $rules The extraction rules to apply.
     * @return array An array containing the extracted data.
     */
    public function extract(string $url, mixed $rules): array
    {
        return Roach::collectSpider(
            UniversalSpider::class,
            new Overrides(startUrls: [$url]),
            context: ['extract_rules' => $rules]
        );
    }

    /**
     * Create and store a scrape record in Redis based on provided URLs and rules.
     *
     * @param User $user The user model.
     * @param string $url  The URL to be scraped.
     * @param mixed $rules  The extraction rules for scraping.
     * @param bool $async Flag to determine if scraping should be processed asynchronously.
     * @return ScrapeRecord  The scrape record data.
     */
    public function saveScrapeRecord(
        User $user,
        string $url,
        mixed $rules,
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
            ScrapeWebsite::dispatch($scrapeRecord, $url, $rules);

            // Set the status to 'in-progress'
            $scrapeRecord->update(['status' => 'in-progress']);

            // Return the scrape record data
            return $scrapeRecord;
        }

        // Extract data from the provided URLs and rules
        $items = $this->extract($url, $rules);

        // Store the extracted data
        $result = collect($items)->map(function ($item) {
            return $item->all();
        });

        // Update scrape record with the result and status
        $scrapeRecord->update(['result' => $result, 'status' => 'completed']);

        // Return the scrape record data
        return $scrapeRecord;
    }
}
