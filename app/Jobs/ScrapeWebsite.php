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
    public function __construct(
        ScrapeRecord $scrapeRecord,
        array $urls,
        mixed $rules
    ) {
        $this->scrapeRecord = $scrapeRecord;
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

            // Update scrape record with the result and status
            $this->scrapeRecord->update([
                'results' => $results,
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
