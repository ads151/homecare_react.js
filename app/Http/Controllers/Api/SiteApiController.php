<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteApi;
use Illuminate\Http\Request;

/** Read-only JSON for the Next.js website. */
class SiteApiController extends Controller
{
    public function site()
    {
        return response()->json(SiteApi::site());
    }

    public function page(Request $request, string $slug)
    {
        $data = SiteApi::page(strtolower($slug), $request->boolean('preview'));

        return $data
            ? response()->json($data)
            : response()->json(['ok' => false, 'message' => 'Page not found'], 404);
    }

    public function sitemap()
    {
        return response()->json(['pages' => SiteApi::sitemap()]);
    }
}
