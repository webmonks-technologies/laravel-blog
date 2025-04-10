<?php


namespace WebMonksBlog\Middleware;

use Closure;
use WebMonksBlog\Models\WebMonksConfiguration;

class PackageSetup
{
    public function handle($request, Closure $next)
    {
        $initial_setup = WebMonksConfiguration::get('INITIAL_SETUP');
        if (!$initial_setup){
            return redirect( route('webmonksblog.admin.setup') );
        }

        return $next($request);
    }
}
