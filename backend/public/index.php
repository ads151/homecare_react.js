<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
 * Server pre-check: shows a clear message instead of "500 Server Error"
 * when the hosting is not ready yet (wrong PHP version, missing files).
 */
(function () {
    $root = dirname(__DIR__);
    $problem = null;

    if (version_compare(PHP_VERSION, '8.3.0', '<')) {
        $problem = 'This website needs <b>PHP 8.3 or newer</b>. Your server is using PHP '.PHP_VERSION.'.'
            .'<br><br><b>Fix:</b> Hostinger hPanel → Websites → Manage → Advanced → <b>PHP Configuration</b> → choose <b>PHP 8.3</b> (or 8.4) → Update. Then reload this page.';
    } elseif (! is_file($root.'/vendor/autoload.php')) {
        $problem = 'The <b>vendor</b> folder is missing (Laravel library files).'
            .'<br><br>This happens when the site is deployed from the GitHub <b>main</b> branch.'
            .'<br><br><b>Fix (choose one):</b><ul>'
            .'<li>Upload <b>homecare-website-laravel.zip</b> with File Manager and Extract it in public_html, <b>or</b></li>'
            .'<li>In hPanel → Advanced → GIT, deploy the branch <b>production</b> instead of main.</li></ul>';
    } else {
        foreach (['storage/app/private', 'storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
            if (! is_dir($root.'/'.$dir)) {
                @mkdir($root.'/'.$dir, 0755, true);
            }
            if (! is_writable($root.'/'.$dir)) {
                $problem = 'The folder <b>'.$dir.'</b> is not writable.'
                    .'<br><br><b>Fix:</b> File Manager → right-click the <b>storage</b> and <b>bootstrap/cache</b> folders → Permissions → <b>755</b> (tick "apply to subfolders").';
                break;
            }
        }
        // First visit: create .env with a new secret key (used by the installer)
        if (! $problem && ! is_file($root.'/.env') && is_file($root.'/.env.example')) {
            $env = file_get_contents($root.'/.env.example');
            $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $env);
            if (@file_put_contents($root.'/.env', $env) === false) {
                $problem = 'Could not create the <b>.env</b> file. File Manager → public_html → Permissions → <b>755</b>.';
            }
        }
    }

    if ($problem) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 60');
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Website setup needed</title>'
            .'<style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:#eef4f7;margin:0;padding:24px;color:#1f2937;line-height:1.6}'
            .'.b{max-width:640px;margin:50px auto;background:#fff;border:2px solid #f59e0b;border-radius:16px;padding:28px}h1{font-size:1.3rem;color:#b45309;margin:0 0 12px}</style></head>'
            .'<body><div class="b"><h1>&#9888; Website setup needed</h1><p>'.$problem.'</p></div></body></html>';
        exit;
    }
})();

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
