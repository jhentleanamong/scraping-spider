<?php

namespace App\Services;

use App\Spiders\UniversalSpider;
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
}
