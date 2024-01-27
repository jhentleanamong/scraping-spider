<?php

namespace App\Services;

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
     * Formats the scrape record from Redis.
     *
     * @param array $data The data from Redis.
     * @return array The formatted data.
     */
    public function formatRecord(array $data): array
    {
        return [
            'id' => $data['id'],
            'urls' => json_decode($data['urls']),
            'extract_rules' => json_decode($data['extract_rules']),
            'results' => json_decode($data['results']),
            'status' => $data['status'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];
    }

    /**
     * Create and store a scrape record in Redis based on provided URLs and rules.
     *
     * @param array $urls  The URLs to be scraped.
     * @param mixed $rules  The extraction rules for scraping.
     * @return array  The formatted scrape record data.
     */
    public function saveScrapeRecord(array $urls, mixed $rules): array
    {
        $id = Str::orderedUuid();
        $key = 'scrape_record:' . $id;

        // Store initial scrape record data in Redis
        Redis::hmset($key, [
            'id' => $id,
            'urls' => json_encode($urls),
            'extract_rules' => $rules,
            'results' => '',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set the status to 'in-progress' in Redis
        Redis::hset($key, 'status', 'in-progress');

        // Extract data from the provided URLs and rules
        $items = $this->extract($urls, $rules);

        // Transform and store the extracted data and update the status in Redis
        $results = collect($items)->map(function ($item) {
            return $item->all();
        });

        // Update Redis with the result and status
        Redis::hmset($key, [
            'results' => json_encode($results),
            'status' => 'completed',
        ]);

        // Return the formatted scrape record data
        return $this->formatRecord(Redis::hgetall($key));
    }
}
