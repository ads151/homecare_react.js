<?php

use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// One-time installer (locked after installation)
Route::get('/install', [InstallController::class, 'show']);
Route::post('/install', [InstallController::class, 'store']);

// Enquiry forms (old /send.php links still work)
Route::post('/send', [EnquiryController::class, 'store'])->middleware('throttle:20,1');
Route::post('/send.php', [EnquiryController::class, 'store'])->middleware('throttle:20,1');

Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
Route::get('/robots.txt', [SiteController::class, 'robots']);

// Website pages: /  and  /page-slug
Route::get('/', [SiteController::class, 'show']);
Route::get('/{slug}', [SiteController::class, 'show'])
    ->where('slug', '(?!admin|livewire|filament|install|up\b)[A-Za-z0-9][A-Za-z0-9-]*(\.php)?');
