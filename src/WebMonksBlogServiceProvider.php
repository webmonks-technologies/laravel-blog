<?php

namespace WebMonksBlog;

use WebMonksBlog\Models\WebMonksPostTranslation;
use Illuminate\Support\ServiceProvider;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Laravel\Fulltext\Commands\Index;
use WebMonksBlog\Laravel\Fulltext\Commands\IndexOne;
use WebMonksBlog\Laravel\Fulltext\Commands\UnindexOne;
use WebMonksBlog\Laravel\Fulltext\ModelObserver;
use WebMonksBlog\Laravel\Fulltext\Search;
use WebMonksBlog\Laravel\Fulltext\SearchInterface;

class WebMonksBlogServiceProvider extends ServiceProvider
{

    protected $commands = [
        Index::class,
        IndexOne::class,
        UnindexOne::class,
    ];
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if (config("webmonksblog.search.search_enabled") == false) {
            // if search is disabled, don't allow it to sync.
            ModelObserver::disableSyncingFor(WebMonksPostTranslation::class);
        }

        if (config("webmonksblog.include_default_routes", true)) {
            include(__DIR__ . "/routes.php");
        }

        foreach ([
                     '2020_10_16_005400_create_web_monks_categories_table.php',
                     '2020_10_16_005425_create_web_monks_category_translations_table.php',
                     '2020_10_16_010039_create_web_monks_posts_table.php',
                     '2020_10_16_010049_create_web_monks_post_translations_table.php',
                     '2020_10_16_121230_create_web_monks_comments_table.php',
                     '2020_10_16_121728_create_web_monks_uploaded_photos_table.php',
                     '2020_10_16_004241_create_web_monks_languages_table.php',
                     '2020_10_22_132005_create_web_monks_configurations_table.php',
                     '2016_11_04_152913_create_laravel_fulltext_table.php'
                 ] as $file) {

            $this->publishes([
                __DIR__ . '/../migrations/' . $file => database_path('migrations/' . $file)
            ]);

        }

        $this->publishes([
            __DIR__ . '/Views/webmonksblog' => base_path('resources/views/vendor/webmonksblog'),
            __DIR__ . '/Config/webmonksblog.php' => config_path('webmonksblog.php'),
            __DIR__ . '/css/WebMonksblog_admin_css.css' => public_path('WebMonksblog_admin_css.css'),
            __DIR__ . '/css/webmonks-blog.css' => public_path('webmonks-blog.css'),
            __DIR__ . '/css/admin-setup.css' => public_path('admin-setup.css'),
            __DIR__ . '/js/webmonks-blog.js' => public_path('webmonks-blog.js'),
        ]);


    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            SearchInterface::class,
            Search::class
        );

        // for the admin backend views ( view("webmonksblog_admin::BLADEFILE") )
        $this->loadViewsFrom(__DIR__ . "/Views/webmonksblog_admin", 'webmonksblog_admin');
        // for public facing views (view("webmonksblog::BLADEFILE")):
        // if you do the vendor:publish, these will be copied to /resources/views/vendor/webmonksblog anyway
        $this->loadViewsFrom(__DIR__ . "/Views/webmonksblog", 'webmonksblog');
    }

}
