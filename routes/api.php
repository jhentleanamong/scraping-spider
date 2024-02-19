<?php

use App\Http\Controllers\Api\ScraperController;
use App\Http\Controllers\Api\ScrapeRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Group routes with 'auth:sanctum' middleware for API token authentication
Route::middleware('auth:sanctum')->group(function () {
    // Retrieve all scraping jobs
    Route::get('/scrape-records', [
        ScrapeRecordController::class,
        'index',
    ])->name('api.scrape-records.index');

    // Register a new scraping job
    Route::post('/scrape-records', [
        ScrapeRecordController::class,
        'store',
    ])->name('api.scrape-records.store');

    // Retrieve details of a specific scraping job by its ID
    Route::get('/scrape-records/{scrapeRecord}', [
        ScrapeRecordController::class,
        'show',
    ])->name('api.scrape-records.show');

    // Define the route for deleting a specific scrape record by its ID
    Route::delete('/scrape-records/{scrapeRecord}', [
        ScrapeRecordController::class,
        'destroy',
    ])->name('api.scrape-records.destroy');
});
