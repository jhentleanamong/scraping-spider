<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RoachPHP\Spider\Configuration\Overrides;

class ScraperController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return urlencode('{
        //     "title" : {
        //         "selector": "h1",
        //         "output": "text",
        //         "type": "item"
        //     },
        //     "link": {
        //         "selector": "h1 a",
        //         "output": "@href",
        //         "type": "item"
        //     },
        //     "tag": {
        //         "selector": ".tag",
        //         "output": "text",
        //         "type": "list"
        //     },
        //     "quotes": {
        //         "selector": ".quote",
        //         "type": "list",
        //         "output": {
        //             "author": {
        //                 "selector": ".author",
        //                 "output": "text",
        //                 "type": "item"
        //             },
        //             "quote": {
        //                 "selector": ".text",
        //                 "output": "text",
        //                 "type": "item"
        //             }
        //         }
        //     }
        // }');

        $validated = $request->validate([
            'url' => ['required'],
            'extract_rules' => ['nullable', 'string'],
        ]);

        // return json_decode($validated['extract_rules'], true);

        $items = \RoachPHP\Roach::collectSpider(
            \App\Spiders\UniversalSpider::class,
            new Overrides(startUrls: [$validated['url']]),
            context: ['extract_rules' => $validated['extract_rules']]
        );

        return array_map(fn($item) => $item->all(), $items);
    }
}
