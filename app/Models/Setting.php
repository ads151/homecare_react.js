<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Keys stored encrypted in the database. */
    public const SECRET_KEYS = ['smtp_password'];

    protected const CACHE_KEY = 'site_settings_v1';

    protected static ?array $memo = null;

    /**
     * All settings as one array (same shape as the old settings.php),
     * default values first, database values on top.
     */
    public static function allSettings(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            $out = [];
            foreach (static::query()->get(['key', 'value']) as $row) {
                $out[$row->key] = json_decode((string) $row->value, true);
            }
            return $out;
        });

        foreach (self::SECRET_KEYS as $k) {
            if (! empty($stored[$k]) && is_string($stored[$k])) {
                try {
                    $stored[$k] = Crypt::decryptString($stored[$k]);
                } catch (\Throwable) {
                    $stored[$k] = '';
                }
            }
        }

        return static::$memo = array_replace(config('site_defaults', []), $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allSettings();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /** Save many settings at once. */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::SECRET_KEYS, true)) {
                if ($value === null || $value === '') {
                    continue; // empty password field = keep the old one
                }
                $value = Crypt::encryptString((string) $value);
            }
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );
        }
        static::flush();
    }

    public static function flush(): void
    {
        static::$memo = null;
        Cache::forget(self::CACHE_KEY);
        \App\Support\Frontend::refreshLater();
    }
}
