<?php

namespace App\Support;

class Installer
{
    public static function lockFile(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function isInstalled(): bool
    {
        return is_file(static::lockFile());
    }

    public static function markInstalled(): void
    {
        @file_put_contents(static::lockFile(), 'Installed on '.date('c'));
    }

    /** @return array<int, array{label:string, ok:bool, hint:string}> */
    public static function requirements(): array
    {
        $checks = [
            ['label' => 'PHP version 8.3 or newer (you have '.PHP_VERSION.')', 'ok' => version_compare(PHP_VERSION, '8.3.0', '>='), 'hint' => 'Hostinger hPanel → Advanced → PHP Configuration → choose PHP 8.3 or 8.4.'],
        ];
        foreach (['pdo_mysql', 'mbstring', 'openssl', 'intl', 'fileinfo', 'tokenizer', 'ctype', 'json', 'xml', 'dom', 'curl'] as $ext) {
            $checks[] = ['label' => 'PHP extension: '.$ext, 'ok' => extension_loaded($ext), 'hint' => 'hPanel → PHP Configuration → PHP Extensions → tick "'.$ext.'".'];
        }
        foreach (['storage', 'storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache', 'public/uploads'] as $dir) {
            $checks[] = ['label' => 'Folder writable: '.$dir, 'ok' => is_writable(base_path($dir)), 'hint' => 'File Manager → right-click the folder → Permissions → 755.'];
        }
        $env = base_path('.env');
        $checks[] = ['label' => 'File writable: .env', 'ok' => is_file($env) ? is_writable($env) : is_writable(base_path()), 'hint' => 'File Manager → .env → Permissions → 644.'];

        return $checks;
    }

    public static function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $env = is_file($path) ? file_get_contents($path) : (is_file(base_path('.env.example')) ? file_get_contents(base_path('.env.example')) : '');
        foreach ($values as $key => $value) {
            $value = (string) $value;
            if ($value === '' || preg_match('/[\s#"\'$\\\\=]/', $value)) {
                // single quotes = taken literally (no ${VAR} expansion)
                $value = ! str_contains($value, "'")
                    ? "'".$value."'"
                    : '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
            }
            $line = $key.'='.$value;
            $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*$/m';
            if (preg_match($pattern, $env)) {
                $env = preg_replace_callback($pattern, fn () => $line, $env, 1);
            } else {
                $env = rtrim($env)."\n".$line."\n";
            }
        }
        if (file_put_contents($path, $env) === false) {
            throw new \RuntimeException('Could not write the .env file. Please set its permission to 644.');
        }
    }
}
