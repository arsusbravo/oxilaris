<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'nl', 'id'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $locale = $user->ui_locale ?: $this->fromBrowser($request);
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function fromBrowser(Request $request): string
    {
        // Accept-Language: nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7
        foreach (explode(',', $request->header('Accept-Language', '')) as $part) {
            $lang = strtolower(trim(explode(';', $part)[0])); // e.g. "nl-nl"
            $primary = explode('-', $lang)[0];                // e.g. "nl"
            if (in_array($primary, self::SUPPORTED, true)) {
                return $primary;
            }
        }

        return 'en';
    }
}
