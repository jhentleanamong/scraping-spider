<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapeRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'details_url' => $this->details_url,
            'urls' => $this->urls,
            'extract_rules' => $this->extract_rules,
            'results' => $this->results,
            'status' => $this->status,
        ];
    }
}
