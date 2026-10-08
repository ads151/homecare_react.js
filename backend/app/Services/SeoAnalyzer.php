<?php

namespace App\Services;

use App\Models\Page;

/**
 * On-page SEO checker (like Yoast / RankMath).
 * Renders the real page HTML and scores it out of 100.
 */
class SeoAnalyzer
{
    public static function analyze(Page $page): array
    {
        try {
            $html = SiteRenderer::render($page, true);
        } catch (\Throwable $e) {
            return [
                'score' => 0,
                'checks' => [['status' => 'bad', 'label' => 'Page could not be rendered', 'msg' => $e->getMessage(), 'points' => 0, 'max' => 0]],
                'title' => '', 'description' => '', 'words' => 0, 'url' => $page->slug,
            ];
        }

        return static::analyzeHtml($html, (string) $page->focus_keyword, (string) $page->slug, (bool) ($page->robots_index ?? true));
    }

    public static function analyzeHtml(string $html, string $keywordInput, string $slug, bool $indexable = true): array
    {
        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        $xp = new \DOMXPath($doc);

        $title = trim((string) ($xp->query('//title')->item(0)?->textContent ?? ''));
        $desc = '';
        foreach ($xp->query('//meta[@name="description"]') as $m) {
            $desc = trim($m->getAttribute('content'));
        }

        $main = $xp->query('//main')->item(0) ?? $doc->documentElement;
        // Ignore forms / popups / scripts when counting words
        foreach (['.//form', './/script', './/style', './/iframe'] as $q) {
            foreach (iterator_to_array($xp->query($q, $main)) as $n) {
                $n->parentNode?->removeChild($n);
            }
        }
        $text = preg_replace('/\s+/u', ' ', trim((string) $main->textContent));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

        $h1s = [];
        foreach ($xp->query('.//h1', $main) as $h) {
            $h1s[] = trim($h->textContent);
        }
        $h2s = [];
        foreach ($xp->query('.//h2|.//h3', $main) as $h) {
            $h2s[] = trim($h->textContent);
        }
        $firstPara = '';
        foreach ($xp->query('.//p', $main) as $p) {
            $t = trim($p->textContent);
            if (mb_strlen($t) > 40) {
                $firstPara = $t;
                break;
            }
        }
        $imgs = $xp->query('.//img', $main);
        $imgCount = $imgs->length;
        $missingAlt = 0;
        $alts = [];
        foreach ($imgs as $img) {
            $a = trim($img->getAttribute('alt'));
            if ($a === '' && ! str_contains($img->getAttribute('class'), 'cta-bg')) {
                $missingAlt++;
            }
            $alts[] = $a;
        }
        $internal = 0;
        $external = 0;
        foreach ($xp->query('.//a[@href]', $main) as $a) {
            $href = $a->getAttribute('href');
            if (preg_match('~^(tel:|mailto:|#|https://wa\.me)~i', $href)) {
                continue;
            }
            if (preg_match('~^https?://~i', $href) && ! str_contains($href, request()->getHost())) {
                $external++;
            } else {
                $internal++;
            }
        }

        $kws = array_values(array_filter(array_map('trim', explode(',', $keywordInput))));
        $kw = mb_strtolower($kws[0] ?? '');
        $has = fn (string $hay) => $kw !== '' && str_contains(mb_strtolower($hay), $kw);

        $checks = [];
        $add = function (string $label, string $status, string $msg, int $max) use (&$checks) {
            $pts = match ($status) { 'good' => $max, 'ok' => (int) round($max / 2), default => 0 };
            $checks[] = compact('label', 'status', 'msg', 'max') + ['points' => $pts];
        };

        // 1. Focus keyword
        if ($kw === '') {
            $checks[] = ['label' => 'Focus keyword', 'status' => 'bad', 'msg' => 'Set a focus keyword (the main search phrase, e.g. "home nursing in East Delhi"). Keyword checks below are skipped until you add one.', 'points' => 0, 'max' => 0];
        }

        // 2. Title length
        $tl = mb_strlen($title);
        $add('SEO title length', $tl >= 30 && $tl <= 60 ? 'good' : ($tl > 0 && $tl <= 70 ? 'ok' : 'bad'),
            "Title is {$tl} characters. Best: 30–60 characters so Google shows it fully.", 10);

        // 3. Keyword in title
        if ($kw !== '') {
            $pos = mb_strpos(mb_strtolower($title), $kw);
            $add('Focus keyword in SEO title', $pos === false ? 'bad' : ($pos <= 10 ? 'good' : 'ok'),
                $pos === false ? 'Add the focus keyword to the SEO title.' : ($pos <= 10 ? 'Keyword is at the start of the title. Great!' : 'Keyword is in the title. Moving it closer to the start is even better.'), 10);
        }

        // 4. Meta description
        $dl = mb_strlen($desc);
        $add('Meta description length', $dl >= 120 && $dl <= 160 ? 'good' : ($dl >= 70 && $dl <= 175 ? 'ok' : 'bad'),
            $dl === 0 ? 'Write a meta description (the 2-line text under the title in Google).' : "Description is {$dl} characters. Best: 120–160 characters.", 10);

        // 5. Keyword in description
        if ($kw !== '') {
            $add('Focus keyword in meta description', $has($desc) ? 'good' : 'bad',
                $has($desc) ? 'Keyword found in the description.' : 'Use the focus keyword in the meta description.', 8);
        }

        // 6. Keyword in URL
        if ($kw !== '') {
            $slugKw = \Illuminate\Support\Str::slug($kw);
            $inUrl = $slug === 'home' ? true : ($slugKw !== '' && str_contains($slug, $slugKw));
            $partial = ! $inUrl && count(array_filter(explode('-', $slugKw), fn ($w) => strlen($w) > 2 && str_contains($slug, $w))) > 0;
            $add('Focus keyword in URL', $inUrl ? 'good' : ($partial ? 'ok' : 'bad'),
                $inUrl ? 'URL contains the keyword.' : "Use the keyword in the page URL, e.g. /{$slugKw}", 7);
        }

        // 7. H1
        $add('One main heading (H1)', count($h1s) === 1 ? 'good' : (count($h1s) > 1 ? 'ok' : 'bad'),
            count($h1s) === 1 ? 'Page has exactly one H1 heading.' : (count($h1s) > 1 ? 'Page has '.count($h1s).' H1 headings — keep only one.' : 'Page has no H1 heading — fill the banner heading.'), 8);

        // 8. Keyword in H1
        if ($kw !== '') {
            $inH1 = count(array_filter($h1s, $has)) > 0;
            $add('Focus keyword in main heading (H1)', $inH1 ? 'good' : 'bad',
                $inH1 ? 'Keyword found in the H1.' : 'Use the keyword in the banner (H1) heading.', 7);
        }

        // 9. Keyword in first paragraph
        if ($kw !== '') {
            $add('Focus keyword in first paragraph', $has($firstPara) ? 'good' : 'bad',
                $has($firstPara) ? 'Keyword appears in the introduction.' : 'Use the keyword in the first paragraph of the page.', 6);
        }

        // 10. Content length
        $add('Content length', $words >= 600 ? 'good' : ($words >= 300 ? 'ok' : 'bad'),
            "Page has about {$words} words. Aim for 600+ words (at least 300).", 10);

        // 11. Keyword density
        if ($kw !== '' && $words > 0) {
            $count = substr_count(mb_strtolower($text), $kw);
            $kwWords = max(1, count(preg_split('/\s+/u', $kw)));
            $density = round($count * $kwWords / $words * 100, 2);
            $add('Keyword density', $density >= 0.5 && $density <= 2.5 ? 'good' : ($count > 0 && $density < 4 ? 'ok' : 'bad'),
                "Keyword used {$count} times ({$density}%). Best: 0.5% – 2.5%.", 6);
        }

        // 12. Keyword in subheadings
        if ($kw !== '') {
            $inH2 = count(array_filter($h2s, $has));
            $add('Focus keyword in subheadings (H2/H3)', $inH2 > 0 ? 'good' : 'bad',
                $inH2 > 0 ? "Keyword found in {$inH2} subheading(s)." : 'Use the keyword in at least one section heading.', 5);
        }

        // 13. Image ALT text
        $add('Image ALT text', $imgCount === 0 ? 'ok' : ($missingAlt === 0 ? 'good' : 'bad'),
            $imgCount === 0 ? 'No images on the page. Add at least one image with ALT text.' : ($missingAlt === 0 ? "All {$imgCount} images have ALT text." : "{$missingAlt} image(s) have no ALT text."), 5);

        // 14. Keyword in image ALT
        if ($kw !== '') {
            $inAlt = count(array_filter($alts, $has)) > 0;
            $add('Focus keyword in an image ALT', $inAlt ? 'good' : 'bad',
                $inAlt ? 'An image ALT text contains the keyword.' : 'Add the keyword to at least one image ALT text.', 4);
        }

        // 15. Internal links
        $add('Internal links', $internal >= 2 ? 'good' : ($internal === 1 ? 'ok' : 'bad'),
            "{$internal} links to other pages of your website. Add 2 or more.", 4);

        // Indexing (warning only)
        if (! $indexable) {
            $checks[] = ['label' => 'Google indexing', 'status' => 'bad', 'msg' => 'This page is set to NOINDEX — Google will not show it in search.', 'points' => 0, 'max' => 0];
        }

        $max = array_sum(array_column($checks, 'max'));
        $got = array_sum(array_column($checks, 'points'));
        $score = $max > 0 ? (int) round($got / $max * 100) : 0;
        if ($kw === '') {
            $score = min($score, 60); // without a keyword the score cannot be "good"
        }

        // Bad first, then ok, then good
        $order = ['bad' => 0, 'ok' => 1, 'good' => 2];
        usort($checks, fn ($a, $b) => $order[$a['status']] <=> $order[$b['status']]);

        return [
            'score' => $score,
            'checks' => $checks,
            'title' => $title,
            'description' => $desc,
            'words' => $words,
            'url' => $slug,
        ];
    }

    public static function color(int $score): string
    {
        return $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
    }
}
