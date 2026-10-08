<?php

return [
    // Next.js website address. Can also be set in Admin → Site Settings → Website (Next.js).
    'url' => env('FRONTEND_URL', ''),

    // Shared secret between this backend and the Next.js website (same value in both .env files).
    'secret' => env('FRONTEND_SECRET', ''),
];
