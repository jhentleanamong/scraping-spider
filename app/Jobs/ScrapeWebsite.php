<?php

namespace App\Jobs;

use App\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ScrapeWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The scrape record key.
     *
     * @var string
     */
    public string $key;

    /**
     * The urls of the websites.
     *
     * @var array
     */
    public array $urls;

    /**
     * The provided extract rules.
     *
     * @var mixed
     */
    public mixed $rules;

    /**
     * Create a new job instance.
     */
    public function __construct(string $key, array $urls, mixed $rules)
    {
        $this->key = $key;
        $this->urls = $urls;
        $this->rules = $rules;
    }

    /**
     * Execute the job.
     */
    public function handle(ScraperService $service): void
    {
        try {
            // Extract data from the provided URLs and rules
            $items = $service->extract($this->urls, $this->rules);

            // Transform and store the extracted data and update the status in Redis
            $results = collect($items)->map(function ($item) {
                return $item->all();
            });

            // Update Redis with the result and status
            Redis::hmset($this->key, [
                'results' => json_encode($results),
                'status' => 'completed',
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Update Redis with error status
            Redis::hmset($this->key, [
                'status' => 'failed',
                'updated_at' => now(),
            ]);
        }
    }
}
