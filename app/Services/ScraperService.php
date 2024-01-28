<?php

namespace App\Services;

use App\Jobs\ScrapeWebsite;
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
            'details_url' => $data['details_url'],
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
     * @param bool $async Flag to determine if scraping should be processed asynchronously.
     * @return array  The formatted scrape record data.
     */
    public function saveScrapeRecord(
        array $urls,
        mixed $rules,
        bool $async = false
    ): array {
        $id = Str::orderedUuid();
        $key = 'scrape_record:' . $id;

        // Store initial scrape record data in Redis
        Redis::hmset($key, [
            'id' => $id,
            'details_url' => route('api.jobs.show', $id),
            'urls' => json_encode($urls),
            'extract_rules' => $rules,
            'results' => '',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($async) {
            // Dispatch the job to scrape the websites
            ScrapeWebsite::dispatch($key, $urls, $rules);

            // Set the status to 'in-progress' in Redis
            Redis::hset($key, 'status', 'in-progress');

            // Return the formatted scrape record data
            return $this->formatRecord(Redis::hgetall($key));
        }

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
            'updated_at' => now(),
        ]);

        // Return the formatted scrape record data
        return $this->formatRecord(Redis::hgetall($key));
    }

    /**
     * Retrieves all scrape records matching a specific pattern.
     *
     * @return array An array of formatted scrape records.
     */
    public function getScrapeRecords(): array
    {
        // Define the pattern to search for in Redis keys
        $pattern = 'scrape_record:*';

        // Retrieve keys matching the pattern
        $keys = $this->getKeys($pattern);
        $scrapeRecords = [];

        // Iterate over each key and get its corresponding data
        foreach ($keys as $key) {
            // Get all fields and values for the hash stored at key
            $scrapeRecords[] = $this->formatRecord(Redis::hgetall($key));
        }

        return $scrapeRecords;
    }

    /**
     * Retrieves all keys matching a given pattern using Redis SCAN command.
     *
     * @param string $pattern The pattern to match against keys.
     * @return array An array of keys matching the given pattern.
     */
    private function getKeys(string $pattern): array
    {
        $keys = [];
        $cursor = 0;

        // Use SCAN to iterate over keyspace
        do {
            // SCAN returns a cursor and an array of keys for each iteration
            list($cursor, $result) = Redis::scan($cursor, 'match', $pattern);

            // Merge the current batch of keys into the total result
            $keys = array_merge($keys, $result);
        } while ($cursor); // Continue until SCAN indicates completion

        return $keys;
    }
}
