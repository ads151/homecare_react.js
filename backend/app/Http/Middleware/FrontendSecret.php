<?php

namespace App\Http\Middleware;

use App\Support\Frontend;
use Closure;
use Illuminate\Http\Request;

/** Only the Next.js website (which knows the secret key) may call these API routes. */
class FrontendSecret
{
    public function handle(Request $request, Closure $next)
    {
        $secret = Frontend::secret();
        $given = (string) $request->header('X-Api-Secret', '');

        if ($secret === '' || ! hash_equals($secret, $given)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
