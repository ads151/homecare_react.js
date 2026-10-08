<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Connection to the Next.js website (frontend). */
class Frontend
{
    /** Public website address, e.g. https://www.yourdomain.com (empty = not connected). */
    public static function url(): string
    {
        try {
            $url = (string) Setting::get('frontend_url', '');
        } catch (\Throwable) {
            $url = '';
        }
        if ($url === '') {
            $url = (string) config('frontend.url', '');
        }

        return rtrim(trim($url), '/');
    }

    public static function secret(): string
    {
        return (string) config('frontend.secret', '');
    }

    public static function connected(): bool
    {
        return static::url() !== '';
    }

    public static function pageUrl(string $slug): string
    {
        $base = static::connected() ? static::url() : rtrim(url('/'), '/');

        return $slug === 'home' ? $base.'/' : $base.'/'.$slug;
    }

    /** Ask the website to refresh its cached content (called after every admin save). */
    public static function refresh(): array
    {
        if (! static::connected() || static::secret() === '') {
            return ['ok' => false, 'message' => 'Website URL or secret key is not set.'];
        }
        try {
            $res = Http::timeout(8)->acceptJson()
                ->withHeaders(['X-Api-Secret' => static::secret()])
                ->post(static::url().'/api/revalidate');

            return ['ok' => $res->successful(), 'message' => $res->successful() ? 'Website refreshed.' : 'Website answered with status '.$res->status().'.'];
        } catch (\Throwable $e) {
            Log::warning('[Frontend] refresh failed: '.$e->getMessage());

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /** Refresh once, after the response has been sent (so admin saves stay fast). */
    public static function refreshLater(): void
    {
        static $queued = false;
        if ($queued || ! static::connected()) {
            return;
        }
        $queued = true;
        app()->terminating(fn () => static::refresh());
    }
}
