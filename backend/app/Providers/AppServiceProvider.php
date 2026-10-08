<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Installer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Uploaded files always get the URL of the domain being visited
        // (works for www / non-www and when the domain changes).
        if (! $this->app->runningInConsole()) {
            $root = rtrim(request()->getSchemeAndHttpHost().request()->getBaseUrl(), '/');
            if (str_ends_with($root, '/index.php')) {
                $root = substr($root, 0, -10);
            }
            config(['filesystems.disks.site.url' => $root]);
        }

        if (Installer::isInstalled()) {
            $this->useAdminMailSettings();
        }

        // Any content change → the Next.js website refreshes its cache
        foreach ([\App\Models\Page::class, \App\Models\Service::class] as $model) {
            $model::saved(fn () => \App\Support\Frontend::refreshLater());
            $model::deleted(fn () => \App\Support\Frontend::refreshLater());
        }
    }

    /** Laravel mail (admin "Forgot password") uses the SMTP from Site Settings. */
    protected function useAdminMailSettings(): void
    {
        try {
            $s = Setting::allSettings();
        } catch (\Throwable) {
            return;
        }
        $user = trim((string) ($s['smtp_username'] ?? ''));
        $from = filter_var($s['from_email'] ?? '', FILTER_VALIDATE_EMAIL) ? $s['from_email'] : $user;
        $useSmtp = filter_var($s['smtp_on'] ?? true, FILTER_VALIDATE_BOOLEAN) && ! empty($s['smtp_password']) && ! empty($s['smtp_host']);

        config([
            'mail.default' => $useSmtp ? 'smtp' : 'sendmail',
            'mail.mailers.smtp.scheme' => strtolower((string) ($s['smtp_secure'] ?? 'ssl')) === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $s['smtp_host'] ?? '',
            'mail.mailers.smtp.port' => (int) ($s['smtp_port'] ?? 465),
            'mail.mailers.smtp.username' => $user,
            'mail.mailers.smtp.password' => str_replace(' ', '', (string) ($s['smtp_password'] ?? '')),
            'mail.from.address' => filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : config('mail.from.address'),
            'mail.from.name' => $s['from_name'] ?? ($s['business_name'] ?? 'Website'),
        ]);
    }
}
