<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use App\Services\SiteRenderer;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function show(Request $request, ?string $slug = null)
    {
        $slug = strtolower(trim((string) $slug, '/'));
        if ($slug === 'home' || $slug === 'index' || $slug === 'index.php') {
            return redirect('/', 301);
        }
        if ($slug === '') {
            $slug = 'home';
        }

        if (\App\Support\Frontend::connected() && ! $request->has('preview')) {
            $qs = $request->getQueryString();

            return redirect()->away(\App\Support\Frontend::pageUrl($slug).($qs ? '?'.$qs : ''), 301);
        }

        $page = Page::query()->where('slug', $slug)->first();
        $canPreview = $request->has('preview') && auth()->check();

        if (! $page || (! $page->is_published && ! $canPreview)) {
            return response(SiteRenderer::renderNotFound(), 404)
                ->header('X-Robots-Tag', 'noindex');
        }

        $html = SiteRenderer::render($page);
        $res = response($html)->header('Content-Type', 'text/html; charset=utf-8');
        if (! $page->robots_index || $slug === 'thank-you' || ! $page->is_published) {
            $res->header('X-Robots-Tag', 'noindex'.($page->robots_follow ? '' : ', nofollow'));
        }

        return $res;
    }

    public function sitemap()
    {
        $base = rtrim(Setting::get('website_url') ?: url('/'), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        $pages = Page::query()->where('is_published', true)->where('robots_index', true)->where('in_sitemap', true)
            ->where('slug', '!=', 'thank-you')->orderByRaw("slug = 'home' desc")->orderBy('title')->get();
        foreach ($pages as $p) {
            $loc = $p->slug === 'home' ? $base.'/' : $base.'/'.$p->slug;
            if ($p->canonical_url && $p->canonical_url !== $loc) {
                continue; // canonical points elsewhere
            }
            $xml .= '  <url><loc>'.htmlspecialchars($loc, ENT_XML1).'</loc><lastmod>'.$p->updated_at?->format('Y-m-d').'</lastmod><priority>'.($p->slug === 'home' ? '1.0' : '0.8').'</priority></url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots()
    {
        if (\App\Support\Frontend::connected()) {
            // This is the admin / API address — keep it out of Google
            return response("User-agent: *
Disallow: /
")->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $base = rtrim(Setting::get('website_url') ?: url('/'), '/');
        $txt = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /send\nDisallow: /thank-you\n";
        $extra = trim((string) Setting::get('robots_extra', ''));
        if ($extra !== '') {
            $txt .= $extra."\n";
        }
        $txt .= "\nSitemap: ".$base."/sitemap.xml\n";

        return response($txt)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
