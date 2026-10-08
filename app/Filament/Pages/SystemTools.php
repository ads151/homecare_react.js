<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Models\Media;
use App\Models\Page as SitePage;
use App\Models\Service;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class SystemTools extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'System & Database';

    protected static ?string $navigationLabel = 'System & Database';

    protected static ?string $slug = 'system';

    protected string $view = 'filament.pages.system-tools';

    public string $output = '';

    public static function getNavigationBadge(): ?string
    {
        $n = count(static::pendingMigrations());

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Database update available';
    }

    /** @return array<int, string> */
    public static function pendingMigrations(): array
    {
        try {
            $migrator = app('migrator');
            if (! $migrator->repositoryExists()) {
                return ['(migration table missing)'];
            }
            $files = $migrator->getMigrationFiles(array_merge($migrator->paths(), [database_path('migrations')]));
            $ran = $migrator->getRepository()->getRan();

            return array_values(array_diff(array_keys($files), $ran));
        } catch (\Throwable) {
            return [];
        }
    }

    protected function getViewData(): array
    {
        $dbOk = true;
        $dbName = '';
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
        } catch (\Throwable) {
            $dbOk = false;
        }

        return [
            'pending' => static::pendingMigrations(),
            'info' => [
                'PHP version' => PHP_VERSION,
                'Laravel version' => app()->version(),
                'Filament version' => \Composer\InstalledVersions::getPrettyVersion('filament/filament') ?? '—',
                'Database' => $dbOk ? config('database.default').' · '.basename((string) $dbName) : 'NOT CONNECTED',
                'Pages' => SitePage::count(),
                'Services' => Service::count(),
                'Enquiries' => Lead::count(),
                'Media files' => Media::count(),
                'Upload limit (PHP)' => ini_get('upload_max_filesize'),
                'Server time' => now()->format('d M Y, h:i A'),
            ],
            'output' => $this->output,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('migrate')->label('Update database')->icon(Heroicon::OutlinedCircleStack)
                ->color(fn () => count(static::pendingMigrations()) ? 'warning' : 'gray')
                ->requiresConfirmation()
                ->modalHeading('Update the database?')
                ->modalDescription('Runs all new database updates (migrations). Your content is NOT deleted. It is a good idea to download a backup first.')
                ->modalSubmitActionLabel('Yes, update now')
                ->action(fn () => $this->run('migrate', ['--force' => true], 'Database updated')),

            Action::make('cache')->label('Clear cache')->icon(Heroicon::OutlinedArrowPath)->color('gray')
                ->action(function () {
                    Setting::flush();
                    $this->run('optimize:clear', [], 'Cache cleared');
                }),

            Action::make('seed')->label('Restore missing default content')->icon(Heroicon::OutlinedArrowUturnLeft)->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Adds back any default page, service or setting that was deleted. Existing content is NOT changed.')
                ->action(fn () => $this->run('db:seed', ['--class' => 'Database\\Seeders\\SiteSeeder', '--force' => true], 'Default content checked')),

            Action::make('backup')->label('Download backup')->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->backup()),
        ];
    }

    protected function run(string $command, array $params, string $title): void
    {
        @set_time_limit(300);
        try {
            $code = Artisan::call($command, $params);
            $this->output = '$ php artisan '.$command."\n".trim(Artisan::output());
            Notification::make()->title($code === 0 ? $title : 'Finished with warnings')->{$code === 0 ? 'success' : 'warning'}()->send();
        } catch (\Throwable $e) {
            $this->output = '$ php artisan '.$command."\nERROR: ".$e->getMessage();
            Notification::make()->title('Error')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    protected function backup()
    {
        $data = [
            'created_at' => now()->toIso8601String(),
            'settings' => Setting::query()->get(['key', 'value'])->toArray(),
            'pages' => SitePage::query()->get()->toArray(),
            'services' => Service::query()->get()->toArray(),
            'leads' => Lead::query()->get()->toArray(),
            'media' => Media::query()->get()->toArray(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'website-backup-'.now()->format('Y-m-d-His').'.json',
            ['Content-Type' => 'application/json']
        );
    }
}
