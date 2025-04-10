<?php

namespace WebMonksBlog\Middleware;

use WebMonksBlog\Models\WebMonksConfiguration;
use Closure;
use WebMonksBlog\Models\WebMonksLanguage;

class DetectLanguage
{
    public function handle($request, Closure $next)
    {
        $locale = $request->route('locale');
        $routeWithoutLocale = false;

        if (!$request->route('locale')){
            $routeWithoutLocale = true;
            $locale = WebMonksConfiguration::get('DEFAULT_LANGUAGE_LOCALE');
        }

        $lang = WebMonksLanguage::where('locale', $locale)
            ->where('active', true)
            ->first();

        if (!$lang){
            return abort(404);
        }

        $request->attributes->add([
            'lang_id' => $lang->id,
            'locale' => $lang->locale,
            'routeWithoutLocale' => $routeWithoutLocale
        ]);

        return $next($request);
    }
}
