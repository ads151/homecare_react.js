<?php

use App\Http\Controllers\Api\SiteApiController;
use App\Http\Controllers\EnquiryController;
use App\Http\Middleware\FrontendSecret;
use Illuminate\Support\Facades\Route;

/*
 * JSON API for the Next.js website.
 * Every request must send the header  X-Api-Secret: <FRONTEND_SECRET>
 */
Route::middleware([FrontendSecret::class, 'throttle:600,1'])->group(function () {
    Route::get('/site', [SiteApiController::class, 'site']);
    Route::get('/pages', [SiteApiController::class, 'sitemap']);
    Route::get('/pages/{slug}', [SiteApiController::class, 'page'])->where('slug', '[A-Za-z0-9-]+');
    Route::post('/enquiry', [EnquiryController::class, 'api'])->middleware('throttle:30,1');
});
