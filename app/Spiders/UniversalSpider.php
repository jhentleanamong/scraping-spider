<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class UniversalSpider extends BasicSpider
{
    /**
     * An array of initial URLs for the spider to start crawling.
     *
     * @var array
     */
    public array $startUrls = [];

    /**
     * The spider middleware that should be used for runs of this spider.
     *
     * @var array
     */
    public array $spiderMiddleware = [];

    /**
     * The downloader middleware that should be used for runs of this spider.
     *
     * @var array
     */
    public array $downloaderMiddleware = [];

    /**
     * The item processors that emitted items will be send through.
     *
     * @var array
     */
    public array $itemProcessors = [];

    /**
     * The extensions that should be used for runs of this spider.
     *
     * @var array
     */
    public array $extensions = [];

    /**
     * How many requests are allowed to be sent concurrently.
     *
     * @var int
     */
    public int $concurrency = 2;

    /**
     * The delay (in seconds) between requests. Note that there
     * is no delay between concurrent requests. Instead, Roach
     * will wait for the `$requestDelay` before sending the
     * next "batch" of concurrent requests.
     *
     * @var int
     */
    public int $requestDelay = 2;

    /**
     * Parses the response and returns a generator of items.
     *
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $result = [];

        // Decoding JSON extraction rules from the context
        $extractRules = json_decode($this->context['extract_rules'], true);

        // If no extraction rules are defined, yield a simple item with HTML content
        if (!$extractRules) {
            yield $this->item([$response->html()]);
            return;
        }

        // Iterating over each property and its rules for extraction
        foreach ($extractRules as $property => $rules) {
            $result[$property] = $this->extract($response, $rules);
        }

        // Yielding the final result after extraction
        yield $this->item([
            'url' => $response->getUri(),
            'result' => $result,
        ]);
    }

    /**
     * Extracts a property from the response based on provided rules.
     *
     * @param Response|Crawler $response The response or crawler object.
     * @param array $rules Rules for extraction.
     * @return string|array|null Extracted data or null if not found.
     */
    private function extract(
        Response|Crawler $response,
        array $rules
    ): string|array|null {
        // Choosing extraction method based on the type defined in rules
        return match ($rules['type']) {
            'list' => $this->extractList($response, $rules),
            default => $this->extractItem($response, $rules),
        };
    }

    /**
     * Extract the first element matching the property selector.
     *
     * @param Response|Crawler $response The response or crawler object.
     * @param array $rules Extraction rules for the item.
     * @return string|null Extracted item or null if not found.
     */
    private function extractItem(
        Response|Crawler $response,
        array $rules
    ): ?string {
        // Check if the element exists
        if ($response->filter($rules['selector'])->count() < 1) {
            return null;
        }

        // Check if the output rule starts with '@', indicating it's an attribute extraction
        if (str_starts_with($rules['output'], '@')) {
            // Extract the specified attribute from the selected element
            return $response
                ->filter($rules['selector'])
                ->attr(substr($rules['output'], 1));
        }

        // Extracting text, or HTML based on the output rule
        return match ($rules['output']) {
            'html' => $response->filter($rules['selector'])->html(),
            default => $response->filter($rules['selector'])->text(),
        };
    }

    /**
     * Extract a list of all elements matching the property selector.
     *
     * @param Response|Crawler $response The response or crawler object.
     * @param array $rules Extraction rules for the list.
     * @return array Extracted list of items.
     */
    private function extractList(
        Response|Crawler $response,
        array $rules
    ): array {
        // Handling different output structures (array or single item)
        if (is_array($rules['output'])) {
            $properties = $rules['output'];

            // Extracting multiple properties for each matched node
            return $response
                ->filter($rules['selector'])
                ->each(function (Crawler $node) use ($properties) {
                    $result = [];

                    foreach ($properties as $key => $rules) {
                        $result[$key] = $this->extract($node, $rules);
                    }

                    return $result;
                });
        }

        // Extracting a single property for each matched node
        return $response
            ->filter($rules['selector'])
            ->each(function (Crawler $node) use ($rules) {
                return $this->extractItem($node, $rules);
            });
    }
}
