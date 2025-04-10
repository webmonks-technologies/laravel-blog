<?php

Route::group(['middleware' => ['web'], 'namespace' => '\WebMonksBlog\Controllers'], function () {

    /** The main public facing blog routes - show all posts, view a category, view a single post, also the add comment route */
    Route::group(['prefix' => "/{locale}/".config('webmonksblog.blog_prefix', 'blog')], function () {

        Route::get('/', 'WebMonksReaderController@index')
            ->name('webmonksblog.index');

        Route::get('/search', 'WebMonksReaderController@search')
            ->name('webmonksblog.search');

        Route::get('/category{subcategories}', 'WebMonksReaderController@view_category')->where('subcategories', '^[a-zA-Z0-9-_\/]+$')->name('webmonksblog.view_category');

        Route::get('/{blogPostSlug}',
            'WebMonksReaderController@viewSinglePost')
            ->name('webmonksblog.single');

        // throttle to a max of 10 attempts in 3 minutes:
        Route::group(['middleware' => 'throttle:10,3'], function () {
            Route::post('save_comment/{blogPostSlug}',
                'WebMonksCommentWriterController@addNewComment')
                ->name('webmonksblog.comments.add_new_comment');
        });
    });

    Route::group(['prefix' => config('webmonksblog.blog_prefix', 'blog')], function () {

        Route::get('/', 'WebMonksReaderController@index')
            ->name('webmonksblognolocale.index');

        Route::get('/search', 'WebMonksReaderController@search')
            ->name('webmonksblognolocale.search');

        Route::get('/category{subcategories}', 'WebMonksReaderController@view_category')->where('subcategories', '^[a-zA-Z0-9-_\/]+$')->name('webmonksblognolocale.view_category');

        Route::get('/{blogPostSlug}',
            'WebMonksReaderController@viewSinglePost')
            ->name('webmonksblognolocale.single');

        // throttle to a max of 10 attempts in 3 minutes:
        Route::group(['middleware' => 'throttle:10,3'], function () {
            Route::post('save_comment/{blogPostSlug}',
                'WebMonksCommentWriterController@addNewComment')
                ->name('webmonksblognolocale.comments.add_new_comment');
        });
    });

    /* Admin backend routes - CRUD for posts, categories, and approving/deleting submitted comments */
    Route::group(['prefix' => config('webmonksblog.admin_prefix', 'blog_admin')], function () {

        Route::get('/search',
            'WebMonksAdminController@searchBlog')
            ->name('webmonksblog.admin.searchblog');

        Route::get('/setup', 'WebMonksAdminSetupController@setup')
            ->name('webmonksblog.admin.setup');

        Route::post('/setup-submit', 'WebMonksAdminSetupController@setup_submit')
            ->name('webmonksblog.admin.setup_submit');

        Route::get('/', 'WebMonksAdminController@index')
            ->name('webmonksblog.admin.index');

        Route::get('/add_post',
            'WebMonksAdminController@create_post')
            ->name('webmonksblog.admin.create_post');


        Route::post('/add_post',
            'WebMonksAdminController@store_post')
            ->name('webmonksblog.admin.store_post');

        Route::post('/add_post_toggle',
            'WebMonksAdminController@store_post_toggle')
            ->name('webmonksblog.admin.store_post_toggle');

        Route::get('/edit_post/{blogPostId}',
            'WebMonksAdminController@edit_post')
            ->name('webmonksblog.admin.edit_post');

        Route::post('/edit_post_toggle/{blogPostId}',
            'WebMonksAdminController@edit_post_toggle')
            ->name('webmonksblog.admin.edit_post_toggle');

        Route::post('/edit_post/{blogPostId}',
            'WebMonksAdminController@update_post')
            ->name('webmonksblog.admin.update_post');

        //Removes post's photo
        Route::get('/remove_photo/{slug}/{lang_id}',
            'WebMonksAdminController@remove_photo')
            ->name('webmonksblog.admin.remove_photo');

        Route::group(['prefix' => "image_uploads",], function () {

            Route::get("/", "WebMonksImageUploadController@index")->name("webmonksblog.admin.images.all");

            Route::get("/upload", "WebMonksImageUploadController@create")->name("webmonksblog.admin.images.upload");
            Route::post("/upload", "WebMonksImageUploadController@store")->name("webmonksblog.admin.images.store");
        });

        Route::delete('/delete_post/{blogPostId}',
            'WebMonksAdminController@destroy_post')
            ->name('webmonksblog.admin.destroy_post');

        Route::group(['prefix' => 'comments',], function () {

            Route::get('/',
                'WebMonksCommentsAdminController@index')
                ->name('webmonksblog.admin.comments.index');

            Route::patch('/{commentId}',
                'WebMonksCommentsAdminController@approve')
                ->name('webmonksblog.admin.comments.approve');
            Route::delete('/{commentId}',
                'WebMonksCommentsAdminController@destroy')
                ->name('webmonksblog.admin.comments.delete');
        });

        Route::group(['prefix' => 'categories'], function () {

            Route::get('/',
                'WebMonksCategoryAdminController@index')
                ->name('webmonksblog.admin.categories.index');

            Route::get('/add_category',
                'WebMonksCategoryAdminController@create_category')
                ->name('webmonksblog.admin.categories.create_category');
            Route::post('/store_category',
                'WebMonksCategoryAdminController@store_category')
                ->name('webmonksblog.admin.categories.store_category');

            Route::get('/edit_category/{categoryId}',
                'WebMonksCategoryAdminController@edit_category')
                ->name('webmonksblog.admin.categories.edit_category');

            Route::patch('/edit_category/{categoryId}',
                'WebMonksCategoryAdminController@update_category')
                ->name('webmonksblog.admin.categories.update_category');

            Route::delete('/delete_category/{categoryId}',
                'WebMonksCategoryAdminController@destroy_category')
                ->name('webmonksblog.admin.categories.destroy_category');
        });

        Route::group(['prefix' => 'languages'], function () {

            Route::get('/',
                'WebMonksLanguageAdminController@index')
                ->name('webmonksblog.admin.languages.index');

            Route::get('/add_language',
                'WebMonksLanguageAdminController@create_language')
                ->name('webmonksblog.admin.languages.create_language');
            Route::post('/add_language',
                'WebMonksLanguageAdminController@store_language')
                ->name('webmonksblog.admin.languages.store_language');

            Route::delete('/delete_language/{languageId}',
                'WebMonksLanguageAdminController@destroy_language')
                ->name('webmonksblog.admin.languages.destroy_language');

            Route::post('/toggle_language/{languageId}',
                'WebMonksLanguageAdminController@toggle_language')
                ->name('webmonksblog.admin.languages.toggle_language');
        });
    });
});

