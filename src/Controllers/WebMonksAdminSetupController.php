<?php

namespace WebMonksBlog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebMonksBlog\Helpers;
use WebMonksBlog\Middleware\UserCanManageBlogPosts;
use WebMonksBlog\Models\WebMonksConfiguration;
use WebMonksBlog\Models\WebMonksLanguage;

/**
 * Class WebMonksAdminSetupController
 * Handles initial setup for WebMonks Blog
 */
class WebMonksAdminSetupController extends Controller
{
    /**
     * WebMonksAdminSetupController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);

        if (!is_array(config("webmonksblog"))) {
            throw new \RuntimeException('The config/webmonksblog.php does not exist. Publish the vendor files for the WebMonksBlog package by running the php artisan publish:vendor command');
        }
    }

    /**
     * View all posts
     *
     * @return mixed
     */
    public function setup(Request $request)
    {
        return view("webmonksblog_admin::setup.setup");
    }

    public function setup_submit(Request $request){
        if ($request['locale'] == null){
            return redirect( route('webmonksblog.admin.setup_submit') );
        }
        $language = new WebMonksLanguage();
        $language->active = $request['active'];
        $language->iso_code = $request['iso_code'];
        $language->locale = $request['locale'];
        $language->name = $request['name'];
        $language->date_format = $request['date_format'];

        $language->save();
        if (!WebMonksConfiguration::get('INITIAL_SETUP')){
            WebMonksConfiguration::set('INITIAL_SETUP', true);
            WebMonksConfiguration::set('DEFAULT_LANGUAGE_LOCALE', $request['locale']);
        }

        return redirect( route('webmonksblog.admin.index') );
    }
}
