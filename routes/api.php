<?php

use App\Http\Controllers\Api\ScraperController;
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

Route::middleware('auth:sanctum')->group(function () {
    // Retrieve all scraping jobs
    Route::get('/jobs', [ScraperController::class, 'index'])->name(
        'api.jobs.index'
    );

    // Register a new scraping job
    Route::post('/jobs', [ScraperController::class, 'store'])->name(
        'api.jobs.store'
    );

    // Retrieve details of a specific scraping job by its ID
    Route::get('/jobs/{scrapeRecord}', [
        ScraperController::class,
        'show',
    ])->name('api.jobs.show');

    // Define the route for deleting a specific scrape record by its ID
    Route::delete('/jobs/{scrapeRecord}', [
        ScraperController::class,
        'destroy',
    ])->name('api.jobs.destroy');
});
