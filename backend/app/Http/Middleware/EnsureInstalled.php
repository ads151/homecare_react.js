<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;

/** Sends every visitor to /install until the website is installed. */
class EnsureInstalled
{
    public function handle(Request $request, Closure $next)
    {
        $installed = Installer::isInstalled();
        $isInstallRoute = $request->is('install') || $request->is('install/*');

        if (! $installed && ! $isInstallRoute && ! $request->is('up')) {
            return redirect(rtrim($request->getBaseUrl(), '/').'/install');
        }
        if ($installed && $isInstallRoute) {
            abort(404);
        }

        return $next($request);
    }
}
