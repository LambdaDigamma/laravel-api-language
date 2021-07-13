<?php

namespace Lambdadigamma\LaravelApiLanguage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Locale;

class AcceptLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale($this->getAcceptedLocale($request));

        return $next($request);
    }

    private function getAcceptedLocale(Request $request): string
    {
        $available = Cache::rememberForever(config('language.cache.name', 'resources.lang'), function () {
            return $this->getSupportedLanguages();
        });

        $preferences = collect($request->getLanguages())->map(function ($locale) {
            return Str::replace('_', '-', $locale);
        });

        $preferences->each(function ($preference) use ($available) {
            if (in_array($preference, $available)) {
                return $preference;
            }
        });

        $preferences = $preferences->toArray();

        $user = $request->user();

        if ($user && ! empty($user->locale)) {
            array_unshift($preferences, $user->locale);
        }

        reset($preferences);

        foreach ($preferences as $preference) {
            $preference = Locale::lookup($available, $preference);

            if (! empty($preference)) {
                return $preference;
            }
        }

        return config('app.locale', 'en');
    }

    private function getSupportedLanguages()
    {
        $useAutoscan = config('api-language.use_autoscan_lang_folder') ?? false;

        if ($useAutoscan) {
            return $this->scanLanguages();
        } else {
            return config('api-language.supported_locales') ?? ['en'];
        }
    }

    /**
     * Scan languages folder and return list of available languages.
     *
     * @return array
     */
    private function scanLanguages(): array
    {
        return array_diff(scandir(resource_path('lang')), ['..', '.']) ?: [];
    }
}
