<?php

namespace App\Services;

use App\Spiders\UniversalSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use Spatie\Browsershot\Browsershot;

class ScraperService
{
    /**
     * Extract data from a specified URL using extraction rules.
     *
     * @param string $url A URL to scrape data from.
     * @param mixed $rules The extraction rules to apply.
     * @return array An array containing the extracted data.
     */
    public function extractData(string $url, mixed $rules): array
    {
        return Roach::collectSpider(
            UniversalSpider::class,
            new Overrides(startUrls: [$url]),
            context: ['extract_rules' => $rules]
        );
    }

    /**
     * Takes a screenshot of the specified web page URL.
     *
     * @param string $url The web page URL to capture a screenshot of.
     * @return string The base64 encoded string of the image file.
     */
    public function takeScreenshot(string $url): string
    {
        return Browsershot::url($url)
            ->setScreenshotType('jpeg', 100)
            ->windowSize(1920, 1080)
            ->fullPage()
            ->noSandbox()
            ->base64Screenshot();
    }
}
