<?php


namespace WebMonksBlog\Middleware;
use Closure;
use WebMonksBlog\Models\WebMonksConfiguration;
use WebMonksBlog\Models\WebMonksLanguage;

class LoadLanguage
{

    public function handle($request, Closure $next)
    {
        $default_locale = WebMonksConfiguration::get('DEFAULT_LANGUAGE_LOCALE');
        $lang = WebMonksLanguage::where('locale', $default_locale)
            ->first();

        $request->attributes->add([
            'locale' => $lang->locale,
            'language_id' => $lang->id
        ]);

        return $next($request);
    }
}
