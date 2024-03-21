<?php

namespace App\Jobs;

use App\Models\ScrapeRecord;
use App\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapeWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The scrape record.
     *
     * @var ScrapeRecord
     */
    public ScrapeRecord $scrapeRecord;

    /**
     * The url of the websites.
     *
     * @var string
     */
    public string $url;

    /**
     * The provided extract rules.
     *
     * @var mixed
     */
    public mixed $rules;

    /**
     * Indicates if screenshot the website.
     *
     * @var string
     */
    public bool $screenshot;

    /**
     * Create a new job instance.
     */
    public function __construct(
        ScrapeRecord $scrapeRecord,
        string $url,
        bool $screenshot,
        mixed $rules
    ) {
        $this->scrapeRecord = $scrapeRecord;
        $this->url = $url;
        $this->rules = $rules;
    }

    /**
     * Execute the job.
     */
    public function handle(ScraperService $service): void
    {
        try {
            // Extract data from the provided URL and rules
            $items = $service->extractData($this->url, $this->rules);

            // Store the extracted data
            $result = collect($items)->map(function ($item) {
                return $item->all();
            });

            // Store the extracted data
            $result = [
                'body' => collect($items)->map(function ($item) {
                    return $item->all();
                }),
            ];

            if ($this->screenshot) {
                $result['screenshot'] = $service->takeScreenshot($this->url);
            }

            // Update scrape record with the result and status
            $this->scrapeRecord->update([
                'result' => $result,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            // Update scrape record with error status
            $this->scrapeRecord->update([
                'status' => 'failed',
            ]);
        }
    }
}
