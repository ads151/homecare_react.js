<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Services\SeoAnalyzer;
use Illuminate\Database\Seeder;

/**
 * Copies the original website (text, services, settings) into the database.
 * Safe to run again: existing records are NOT overwritten.
 */
class SiteSeeder extends Seeder
{
    public function run(): void
    {
        // Settings — only add the ones that do not exist yet
        $existing = Setting::query()->pluck('key')->all();
        $new = array_diff_key(config('site_defaults'), array_flip($existing));
        if ($new) {
            Setting::putMany($new);
        }

        $data = json_decode(file_get_contents(__DIR__.'/data/site_content.json'), true);

        $keywords = [
            'home' => 'home nursing in East Delhi',
            'about' => 'home care',
            'contact' => 'home care',
            'home-care-services' => 'home care services in East Delhi',
        ];

        foreach ($data['pages'] as $p) {
            if (Page::query()->where('slug', $p['slug'])->exists()) {
                continue;
            }
            Page::query()->create([
                'title' => $p['title'],
                'slug' => $p['slug'],
                'template' => $p['template'],
                'content' => $p['content'],
                'is_published' => true,
                'is_system' => in_array($p['slug'], ['home', 'thank-you'], true),
                'seo_title' => $p['seo_title'] ?? null,
                'seo_description' => $p['seo_description'] ?? null,
                'focus_keyword' => $keywords[$p['slug']] ?? null,
                'robots_index' => $p['slug'] !== 'thank-you',
                'robots_follow' => true,
                'in_sitemap' => $p['slug'] !== 'thank-you',
            ]);
        }

        if (Service::query()->count() === 0) {
            foreach ($data['services'] as $i => $s) {
                Service::query()->create([
                    'title' => $s['title'],
                    'image' => $s['image'] ?? null,
                    'image_alt' => null,
                    'badge' => $s['badge'] ?? null,
                    'label' => $s['label'] ?? null,
                    'category' => $s['category'] ?? null,
                    'text' => $s['text'] ?? null,
                    'features' => $s['features'] ?? [],
                    'price' => $s['price'] ?? null,
                    'old_price' => $s['old_price'] ?? null,
                    'price_note' => $s['price_note'] ?? null,
                    'button_text' => $s['button_text'] ?? null,
                    'is_active' => (bool) ($s['show'] ?? true),
                    'sort_order' => (int) ($s['order'] ?? $i + 1),
                ]);
            }
        }

        // First SEO scores
        foreach (Page::query()->whereNull('seo_score')->get() as $page) {
            try {
                $page->forceFill(['seo_score' => SeoAnalyzer::analyze($page)['score']])->saveQuietly();
            } catch (\Throwable) {
                // score is calculated again when the page is saved
            }
        }
    }
}
