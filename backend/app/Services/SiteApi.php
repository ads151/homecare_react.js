<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Setting;
use App\Support\Frontend;

/**
 * Builds the JSON the Next.js website reads.
 * Uses the same helper functions as the PHP design, so text placeholders,
 * links, prices and SEO data come out exactly the same.
 */
class SiteApi
{
    /** Settings the public website must never receive. */
    public const PRIVATE_SETTINGS = [
        'smtp_on', 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'smtp_password',
        'from_email', 'from_name', 'mail_to', 'mail_cc',
        'autoreply_on', 'autoreply_subject', 'autoreply_message',
    ];

    protected static function boot(string $page = 'home', array $content = []): void
    {
        SiteRenderer::boot();
        $settings = Setting::allSettings();
        if (trim((string) ($settings['website_url'] ?? '')) === '' && Frontend::connected()) {
            $settings['website_url'] = Frontend::url();
        }
        $GLOBALS['HC'] = ['settings' => $settings, 'content' => $content, 'page' => $page, 'preview' => true, 'faq' => []];
    }

    public static function site(): array
    {
        static::boot();
        $settings = array_diff_key($GLOBALS['HC']['settings'], array_flip(static::PRIVATE_SETTINGS));

        $fields = [];
        foreach (hc_form_fields() as $f) {
            $f['options'] = $f['type'] === 'select' ? hc_select_options($f) : [];
            $f['short_form'] = ! isset($f['short_form']) || on($f['short_form']);
            $fields[] = $f;
        }

        $cards = array_map(fn (array $c) => $c + [
            'details' => hc_card_details($c),
            'price_display' => hc_price($c['price'] ?? ''),
            'old_price_display' => hc_price($c['old_price'] ?? ''),
        ], hc_cards());

        return [
            'settings' => $settings,
            'links' => [
                'tel' => hc_tel(),
                'whatsapp' => hc_wa(),
                'site_url' => hc_site_url(),
            ],
            'form_fields' => $fields,
            'services' => array_values($cards),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /** @return array|null null = page not found / not published */
    public static function page(string $slug, bool $preview = false): ?array
    {
        $page = Page::query()->where('slug', $slug)->first();
        if (! $page || (! $page->is_published && ! $preview)) {
            return null;
        }

        $content = SiteRenderer::contentFor($page);
        static::boot($slug, $content);
        $GLOBALS['HC']['faq'] = hc_collect_faq($content);

        $title = hc_page_title();
        $desc = (string) c('seo_description', '');
        $index = on(c('robots_index', true)) && $slug !== 'thank-you';
        $ogImg = c('og_image', '') ?: (c('hero_image', '') ?: (s('default_og_image', '') ?: s('logo', '')));

        $filled = static::prepare(hc_fill($content));
        unset($filled['seo_title'], $filled['seo_description'], $filled['og_title'], $filled['og_description'], $filled['og_image'], $filled['canonical_url'], $filled['focus_keyword']);

        return [
            'slug' => $page->slug,
            'title' => $page->title,
            'template' => $page->template ?: 'builder',
            'content' => $filled,
            'seo' => [
                'title' => $title,
                'description' => $desc,
                'canonical' => c('canonical_url', '') !== '' ? c('canonical_url') : hc_abs($slug === 'home' ? '' : $slug),
                'index' => $index,
                'follow' => on(c('robots_follow', true)),
                'og_title' => c('og_title', '') !== '' ? c('og_title') : $title,
                'og_description' => c('og_description', '') !== '' ? c('og_description') : $desc,
                'og_image' => $ogImg ? hc_abs_img($ogImg) : '',
                'jsonld' => hc_schema_data(),
            ],
            'updated_at' => $page->updated_at?->toIso8601String(),
        ];
    }

    public static function sitemap(): array
    {
        return Page::query()
            ->where('is_published', true)->where('robots_index', true)->where('in_sitemap', true)
            ->where('slug', '!=', 'thank-you')
            ->whereNull('canonical_url')
            ->orderByRaw("slug = 'home' desc")->orderBy('title')
            ->get(['slug', 'updated_at'])
            ->map(fn (Page $p) => ['slug' => $p->slug, 'updated_at' => $p->updated_at?->toIso8601String()])
            ->all();
    }

    /** Clean up content for the website: safe rich text, map links, video ids. */
    protected static function prepare(array $content): array
    {
        if (isset($content['body'])) {
            $content['body'] = hc_rich($content['body']);
        }
        if (isset($content['map_embed'])) {
            $content['map_embed'] = static::mapSrc($content['map_embed']);
        }
        foreach (['blocks', 'extra_blocks'] as $list) {
            foreach (hc_list($content[$list] ?? []) as $i => $b) {
                if (! is_array($b) || empty($b['type'])) {
                    continue;
                }
                $d = is_array($b['data'] ?? null) ? $b['data'] : [];
                if ($b['type'] === 'rich_text') {
                    $d['body'] = hc_rich($d['body'] ?? '');
                }
                if (isset($d['map_embed'])) {
                    $d['map_embed'] = static::mapSrc($d['map_embed']);
                }
                if ($b['type'] === 'video') {
                    $d['youtube_id'] = hc_youtube_id($d['url'] ?? '');
                }
                $content[$list][$i] = ['type' => $b['type'], 'data' => $d];
            }
            if (isset($content[$list]) && is_array($content[$list])) {
                $content[$list] = array_values($content[$list]);
            }
        }

        return $content;
    }

    protected static function mapSrc($embed): string
    {
        $embed = trim((string) $embed);
        if (preg_match('~src="([^"]+)"~i', $embed, $m)) {
            $embed = html_entity_decode($m[1]);
        }

        return preg_match('~^https://~i', $embed) ? $embed : '';
    }
}
