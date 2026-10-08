<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** One-page installer for shared hosting (no SSH needed). */
class InstallController extends Controller
{
    public function show(Request $request)
    {
        return view('install.index', [
            'checks' => Installer::requirements(),
            'siteUrl' => rtrim($request->getSchemeAndHttpHost().$request->getBaseUrl(), '/'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_url' => ['required', 'url'],
            'frontend_url' => ['nullable', 'url'],
            'db_host' => ['required', 'string', 'max:190'],
            'db_port' => ['required', 'integer'],
            'db_database' => ['required', 'string', 'max:190'],
            'db_username' => ['required', 'string', 'max:190'],
            'db_password' => ['nullable', 'string', 'max:190'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'max:190'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        foreach (Installer::requirements() as $check) {
            if (! $check['ok']) {
                return back()->withInput()->withErrors(['install' => 'Please fix this first: '.$check['label'].' — '.$check['hint']]);
            }
        }

        // 1. Test the database connection
        try {
            new \PDO(
                'mysql:host='.$data['db_host'].';port='.$data['db_port'].';dbname='.$data['db_database'].';charset=utf8mb4',
                $data['db_username'],
                (string) ($data['db_password'] ?? ''),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 10]
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['db_database' => 'Could not connect to the database: '.$e->getMessage().' — check database name, username and password in hPanel → Databases.']);
        }

        @set_time_limit(300);

        try {
            // 2. Write .env
            $key = 'base64:'.base64_encode(random_bytes(32));
            Installer::writeEnv([
                'APP_NAME' => 'Home Care',
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'APP_URL' => rtrim($data['site_url'], '/'),
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_DATABASE' => $data['db_database'],
                'DB_USERNAME' => $data['db_username'],
                'DB_PASSWORD' => (string) ($data['db_password'] ?? ''),
                'FRONTEND_URL' => rtrim((string) ($data['frontend_url'] ?? ''), '/'),
                'FRONTEND_SECRET' => config('frontend.secret') ?: bin2hex(random_bytes(24)),
            ]);

            // 3. Use the new database in this request
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => $data['db_host'],
                'database.connections.mysql.port' => $data['db_port'],
                'database.connections.mysql.database' => $data['db_database'],
                'database.connections.mysql.username' => $data['db_username'],
                'database.connections.mysql.password' => (string) ($data['db_password'] ?? ''),
            ]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 4. Create tables + copy the website content
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\SiteSeeder', '--force' => true]);

            if (! empty($data['frontend_url'])) {
                \App\Models\Setting::putMany(['frontend_url' => rtrim($data['frontend_url'], '/')]);
            }

            // 5. Admin user
            User::query()->updateOrCreate(
                ['email' => strtolower($data['admin_email'])],
                ['name' => $data['admin_name'], 'password' => Hash::make($data['admin_password'])]
            );

            // 6. New secret key + lock the installer
            Installer::writeEnv(['APP_KEY' => $key]);
            Installer::markInstalled();
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['install' => 'Installation error: '.$e->getMessage()]);
        }

        return redirect()->to(rtrim($data['site_url'], '/').'/admin/login')->with('installed', true);
    }
}
