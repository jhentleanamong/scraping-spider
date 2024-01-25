<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class UniversalSpider extends BasicSpider
{
    /**
     * An array of initial URLs for the spider to start crawling.
     */
    public array $startUrls = ['https://quotes.toscrape.com/'];

    /**
     * The spider middleware that should be used for runs
     * of this spider.
     */
    public array $spiderMiddleware = [];

    /**
     * The downloader middleware that should be used for
     * runs of this spider.
     */
    public array $downloaderMiddleware = [];

    /**
     * The item processors that emitted items will be send
     * through.
     */
    public array $itemProcessors = [];

    /**
     * The extensions that should be used for runs of this
     * spider.
     */
    public array $extensions = [];

    /**
     * How many requests are allowed to be sent concurrently.
     */
    public int $concurrency = 2;

    /**
     * The delay (in seconds) between requests. Note that there
     * is no delay between concurrent requests. Instead, Roach
     * will wait for the `$requestDelay` before sending the
     * next "batch" of concurrent requests.
     */
    public int $requestDelay = 2;

    /**
     * Parses the response and returns a generator of items.
     *
     * @return Generator<ParseResult>
     */
    // public function parse(Response $response): Generator
    // {
    //     $extractRules = json_decode($this->context['extract_rules'], true);
    //     $content = [];
    //
    //     // Output:
    //     // text
    //     // html
    //     // @ prefix for attribute
    //     // list
    //
    //     if ($extractRules) {
    //         foreach ($extractRules as $key => $value) {
    //             $result = null;
    //
    //             if ($value['type'] == 'item') {
    //                 if (str_starts_with($value['output'], '@')) {
    //                     $attribute = substr($value['output'], 1);
    //                     $result = $response
    //                         ->filter($value['selector'])
    //                         ->attr($attribute);
    //                 }
    //
    //                 if ($value['output'] == 'text') {
    //                     $result = $response->filter($value['selector'])->text();
    //                 }
    //
    //                 if ($value['output'] == 'html') {
    //                     $result = $response->filter($value['selector'])->html();
    //                 }
    //
    //                 $content[$key] = $result;
    //             }
    //
    //             if ($value['type'] == 'list') {
    //                 /*if (is_array($value['output'])) {
    //                     foreach ($value['output'] as $listOutput) {
    //                         $listOutputResult = $response
    //                             ->filter($value['selector'])
    //                             ->each(function ($node, $i) use ($listOutput) {
    //                                 $listOutputResult = [];
    //
    //                                 // Repeat whole cycle again here
    //
    //                                 // foreach (
    //                                 //     $listOutput
    //                                 //     as $listOutputKey => $listOutputValue
    //                                 // ) {
    //                                 //     $listOutputResult[]
    //                                 // }
    //                             });
    //
    //                         // if (str_starts_with($listValue['output'], '@')) {
    //                         //     $attribute = substr($listValue['output'], 1);
    //                         //
    //                         //     $listOutputResult = $response
    //                         //         ->filter($listValue['selector'])
    //                         //         ->each(function ($node, $i) use (
    //                         //             $attribute
    //                         //         ) {
    //                         //             return $node->attr($attribute);
    //                         //         });
    //                         // }
    //                         //
    //                         // if ($listValue['output'] == 'text') {
    //                         //     $listOutputResult = $response
    //                         //         ->filter($listValue['selector'])
    //                         //         ->each(function ($node, $i) {
    //                         //             return $node->text();
    //                         //         });
    //                         // }
    //                         //
    //                         // if ($listValue['output'] == 'html') {
    //                         //     $listOutputResult = $response
    //                         //         ->filter($listValue['selector'])
    //                         //         ->each(function ($node, $i) {
    //                         //             return $node->html();
    //                         //         });
    //                         // }
    //                         //
    //                         $content[$key] = $listOutputResult;
    //                     }
    //                 } else {*/
    //                 if (str_starts_with($value['output'], '@')) {
    //                     $attribute = substr($value['output'], 1);
    //
    //                     $listResult = $response
    //                         ->filter($value['selector'])
    //                         ->each(function ($node, $i) use ($attribute) {
    //                             return $node->attr($attribute);
    //                         });
    //                 }
    //
    //                 if ($value['output'] == 'text') {
    //                     $listResult = $response
    //                         ->filter($value['selector'])
    //                         ->each(function ($node, $i) {
    //                             return $node->text();
    //                         });
    //                 }
    //
    //                 if ($value['output'] == 'html') {
    //                     $listResult = $response
    //                         ->filter($value['selector'])
    //                         ->each(function ($node, $i) {
    //                             return $node->html();
    //                         });
    //                 }
    //
    //                 $content[$key] = $listResult;
    //                 /*}*/
    //             }
    //         }
    //     }
    //
    //     // dd($extractRules);
    //
    //     yield $this->item(['content' => $content]);
    //
    //     // dd($extractRules);
    //     //
    //     // $items = $response->filter('h1')->text();
    //
    //     // yield $this->item(['url' => $response->getUri(), 'content' => $items]);
    //     //
    //     // dd($items);
    //
    //     // $items = $response
    //     //     ->filter($this->context['filter'])
    //     //     ->each(function ($node, $i) {
    //     //         return $node->text();
    //     //     });
    //     //
    //     // dd($response->html());
    //     //
    //     // yield $this->item([$this->startUrls]);
    // }

    public function parse(Response $response): Generator
    {
        $extractRules = json_decode($this->context['extract_rules'], true);
        $content = [];

        if ($extractRules) {
            foreach ($extractRules as $key => $value) {
                if ($value['type'] == 'item') {
                    $result = $this->extractItem($response, $value);
                    $content[$key] = $result;
                }

                if ($value['type'] == 'list') {
                    $listResult = $this->extractList($response, $value);
                    $content[$key] = $listResult;
                }
            }
        }

        yield $this->item(['content' => $content]);
    }

    /**
     * Extracts content for single items.
     *
     * @param mixed $response The HTTP response object.
     * @param array $value The extraction rules for the item.
     * @return mixed|null The extracted content.
     */
    private function extractItem(mixed $response, array $value): mixed
    {
        $result = null;

        if (str_starts_with($value['output'], '@')) {
            $attribute = substr($value['output'], 1);
            $result = $response->filter($value['selector'])->attr($attribute);
        } elseif ($value['output'] == 'text') {
            $result = $response->filter($value['selector'])->text();
        } elseif ($value['output'] == 'html') {
            $result = $response->filter($value['selector'])->html();
        }

        return $result;
    }

    /**
     * Extracts content for lists.
     *
     * @param mixed $response The HTTP response object.
     * @param array $value The extraction rules for the list.
     * @return array The extracted list content.
     */
    private function extractList(mixed $response, array $value)
    {
        if (is_array($value['output'])) {
            return $response
                ->filter($value['selector'])
                ->each(function ($node, $i) use ($value) {
                    $result = [];

                    foreach ($value['output'] as $childKey => $childValue) {
                        if ($childValue['type'] == 'item') {
                            $result[$childKey] = $this->extractItem(
                                $node,
                                $childValue
                            );
                        }

                        if ($childValue['type'] == 'list') {
                            $result[$childKey] = $this->extractList(
                                $node,
                                $childValue
                            );
                        }
                    }

                    return $result;
                });
        }

        return $response
            ->filter($value['selector'])
            ->each(function ($node, $i) use ($value) {
                $result = null;

                if (str_starts_with($value['output'], '@')) {
                    $attribute = substr($value['output'], 1);
                    $result = $node->attr($attribute);
                } elseif ($value['output'] == 'text') {
                    $result = $node->text();
                } elseif ($value['output'] == 'html') {
                    $result = $node->html();
                }

                return $result;
            });
    }
}
