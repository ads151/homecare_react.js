<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Setting;

/**
 * Renders a website page with the original design.
 * Content comes from the `pages` table, settings from `settings`.
 */
class SiteRenderer
{
    protected static bool $loaded = false;

    public const SEO_KEYS = [
        'focus_keyword', 'seo_title', 'seo_description', 'canonical_url', 'robots_index',
        'robots_follow', 'og_title', 'og_description', 'og_image', 'schema_type',
    ];

    public static function boot(): void
    {
        if (static::$loaded) {
            return;
        }
        require_once resource_path('site/engine.php');
        require_once resource_path('site/layout.php');
        require_once resource_path('site/parts.php');
        static::$loaded = true;
    }

    /** Build the content array a template reads with c(). */
    public static function contentFor(Page $page): array
    {
        $content = is_array($page->content) ? $page->content : [];
        // Builder state from Filament uses UUID keys — keep order, drop keys.
        foreach (['blocks', 'extra_blocks'] as $key) {
            if (isset($content[$key]) && is_array($content[$key])) {
                $content[$key] = array_values($content[$key]);
            }
        }
        $content['template'] = $page->template ?: 'builder';
        foreach (static::SEO_KEYS as $k) {
            $content[$k] = $page->{$k};
        }
        if (empty($content['page_name'])) {
            $content['page_name'] = $page->title;
        }
        if ($page->slug === 'thank-you') {
            $content['robots_index'] = false;
        }

        return $content;
    }

    /**
     * @param  bool  $preview  true = admin preview / SEO check (no tracking codes)
     */
    public static function render(Page $page, bool $preview = false): string
    {
        static::boot();

        $content = static::contentFor($page);

        $GLOBALS['HC'] = [
            'settings' => Setting::allSettings(),
            'content' => $content,
            'page' => $page->slug === 'home' ? 'home' : $page->slug,
            'preview' => $preview,
        ];
        $GLOBALS['HC']['faq'] = hc_collect_faq($content);

        return static::renderTemplate($content['template']);
    }

    public static function renderNotFound(): string
    {
        static::boot();

        $GLOBALS['HC'] = [
            'settings' => Setting::allSettings(),
            'content' => [
                'template' => '404',
                'seo_title' => 'Page Not Found | '.Setting::get('business_name', 'Home Care'),
                'page_name' => 'Page Not Found',
                'hero_title' => 'Page Not Found',
                'hero_text' => 'Sorry, the page you are looking for does not exist or has been moved.',
                'hero_image' => 'images/pages/page-hero.jpg',
                'robots_index' => false,
            ],
            'page' => '404',
            'preview' => false,
            'faq' => [],
        ];

        return static::renderTemplate('404');
    }

    protected static function renderTemplate(string $template): string
    {
        $tpl = preg_replace('/[^a-z0-9-]/', '', $template);
        $file = resource_path('site/templates/'.$tpl.'.php');
        if (! is_file($file)) {
            $file = resource_path('site/templates/builder.php');
        }

        $level = ob_get_level();
        ob_start();
        try {
            (static function ($__file) {
                include $__file;
            })($file);
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean();
    }
}
