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
     * @param string $url The URL to scrape data from.
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
     * Formats the scrape record from Redis.
     *
     * @param array $data The data from Redis.
     * @return array The formatted data.
     */
    public function formatRecord(array $data): array
    {
        return [
            'id' => $data['id'],
            'url' => $data['url'],
            'extract_rules' => $data['extract_rules'],
            'result' => $data['result'],
            'status' => $data['status'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];
    }
}
