<?php

class MainTest extends \Tests\TestCase
{

//    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /*
     *
     *
     *

    This is one great big huge test file that checks most of the features (not 100% yet) of this package.


    List of all main routes, and if they are covered by any tests:
    There might be some additional tests still to be written (For example we create a new post, but don't assign any categories to it at the moment)

    UNTESTED - todo:
    Testing the author_email, author_website.

    /blog/...
        webmonksblog.index                       YES
        webmonksblog.view_category               no - but this is basically webmonksblog.index
        webmonksblog.single                      YES
        webmonksblog.comments.add_new_comment    YES - tested multiple times with/without basic captcha on/off/correct/incorrect.
                                                Also tested with diff configs for comment form:
                                                    disabled
                                                    built_in
                                                    disqus
                                                    custom

    /blog_admin/...
        webmonksblog.admin.index                 YES
        webmonksblog.admin.create_post           no - but is just a form
        webmonksblog.admin.store_post            YES
        webmonksblog.admin.edit_post             YES - but no extra checks
        webmonksblog.admin.update_post           YES
        webmonksblog.admin.destroy_post          YES

     /blog_admin/comments/...

            webmonksblog.admin.comments.index    YES
            webmonksblog.admin.comments.approve  YES
            webmonksblog.admin.comments.delete   YES

     /blog_admin/categories/...

            webmonksblog.admin.categories.index
            webmonksblog.admin.categories.create_category    no - but is just a form
            webmonksblog.admin.categories.store_category     YES
            webmonksblog.admin.categories.edit_category      no - but is just a form
            webmonksblog.admin.categories.update_category
            webmonksblog.admin.categories.destroy_category   YES



     *
     *
     *
     */

    public function testFilesArePresent()
    {
        $this->assertFileExists(config_path("webmonksblog.php"), "/config/webmonksblog.php should exist - currently no file with that filename is found");
        $this->assertTrue(is_array(include(config_path("webmonksblog.php"))), "/config/webmonksblog.php should exist - currently no file with that filename is found");
    }

    public function testImageSizesAreSane()
    {

        $this->assertTrue(count(config("webmonksblog.image_sizes")) >=  3);

        foreach (config("webmonksblog.image_sizes") as $image_key => $image_info) {

            $this->assertArrayHasKey("w", $image_info);
            $this->assertArrayHasKey("h", $image_info);
            $this->assertArrayHasKey("name", $image_info);
            $this->assertArrayHasKey("enabled", $image_info);
            $this->assertArrayHasKey("basic_key", $image_info);

            $this->assertTrue(is_bool($image_info['enabled']));
            $this->assertTrue(is_int($image_info['w']));
            $this->assertTrue(is_int($image_info['h']));
            $this->assertTrue(is_string($image_info['name']));
            $this->assertTrue(is_string($image_info['basic_key']));
            $this->assertTrue(is_string($image_key));



        }

    }


    public function testUserHasNanManageWebMonksBlogPostsMethod()
    {

        $this->assertTrue(method_exists(\App\User::class, "canManageWebMonksBlogPosts"), "Your User model must have the canManageWebMonksBlogPosts method");

        $user = new \App\User();
        $this->assertTrue(is_bool($user->canManageWebMonksBlogPosts()));

    }

    // more tests coming soon

    public function testCanSeeAdminPanel()
    {

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");

        \Auth::logout();
        // without a logged in user, should give error
        $response = $this->get($admin_panel_url);
        $response->assertStatus(401);


//        $user = new \App\User();


        $user = $this->create_admin_user();

        $response = $this->get($admin_panel_url);
        $response->assertStatus(200);

        // check user can see admin area:
        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $response = $this->get($admin_panel_url);
        // check if we can see the admin panel correctly
        $response->assertStatus(200);
        $response->assertSee("All Posts");
        $response->assertSee("Add Post");
        $response->assertSee("All Comments");
        $response->assertSee("All Categories");
        $response->assertSee("Add Category");


        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
//        $user=$this->create_admin_user();

        $this->assertTrue($user->canManageWebMonksBlogPosts());


        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);

        $response->assertSessionHasNoErrors();
//        dump($response);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


    }

    public function testCanCreatePost()
    {


        $user = $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


    }


    public function testCanCreatePostThenEditIt()
    {


        $user = $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);

        $justCreatedRow = \WebMonksBlog\Models\WebMonksPost::where("slug", $new_object_vals['slug'])->firstOrFail();


        $new_object_vals['title'] = "New title " . str_random();
        $this->assertDatabaseMissing('web_monks_posts', ['title' => $new_object_vals['title']]);
        $response = $this->patch($admin_panel_url . "/edit_post/" . $justCreatedRow->id, $new_object_vals);
        $response->assertStatus(302);
        $this->assertDatabaseHas('web_monks_posts', ['title' => $new_object_vals['title']]);


    }


    public function testCreatePostThenCheckIsViewableToPublic()
    {


        $user = $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        $new_object_vals['slug'] = "slug123" . str_random();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);


        // check we don't see it at moment
        $response = $this->get(config("webmonksblog.blog_prefix", "blog"));
        $response->assertDontSee($new_object_vals['slug']);

        // must clear the cache
        \Artisan::call('cache:clear');

        $response->assertDontSee($new_object_vals['slug']);

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);

        // logout - so we are guest user
        \Auth::logout();
        $response = $this->get(config("webmonksblog.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertSee($new_object_vals['slug']);


        // must clear the cache
        \Artisan::call('cache:clear');

        $response->assertSee($new_object_vals['slug']);
        $response->assertSee($new_object_vals['title']);


        // now check single post is viewable

        $response = $this->get(route("webmonksblog.single", $new_object_vals['slug']));
        $response->assertStatus(200);
        $response->assertSee($new_object_vals['slug']);
        $response->assertSee($new_object_vals['title']);


    }


    public function testCreatePostWithNotPublishedThenCheckIsNotViewableToPublic()
    {


        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        list($new_object_vals, $search_for_obj) = $this->prepare_post_creation();

        $new_object_vals['is_published'] = false;

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        \Auth::logout();

        $response = $this->get(config("webmonksblog.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($new_object_vals['slug']);


        // now check single post is viewable

        $response = $this->get(config("webmonksblog.blog_prefix", "blog") . "/" . $new_object_vals['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($new_object_vals['slug']);
        $response->assertDontSee($new_object_vals['title']);


    }


    public function testCreatePostWithFuturePostedAtThenCheckIsNotViewableToPublic()
    {


        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        list($new_object_vals, $search_for_obj) = $this->prepare_post_creation();

        $new_object_vals['posted_at'] = \Carbon\Carbon::now()->addMonths(12);

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        \Auth::logout();

        $response = $this->get(config("webmonksblog.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($new_object_vals['slug']);


        // now check single post is viewable

        $response = $this->get(config("webmonksblog.blog_prefix", "blog") . "/" . $new_object_vals['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($new_object_vals['slug']);
        $response->assertDontSee($new_object_vals['title']);


    }


    public function testCreatePostThenCheckCanCreateCommentThenApproveCommentWithBasicCaptchaEnabledAndWrongAnswer()
    {


        \Config::set('webmonksblog.comments.auto_approve_comments', false);
        \Config::set('webmonksblog.captcha.captcha_enabled', true);
        \Config::set('webmonksblog.captcha.captcha_type', \WebMonksBlog\Captcha\Basic::class);
        $captcha = new \WebMonksBlog\Captcha\Basic();
        \Config::set('webmonksblog.captcha.basic_question', "a test question");
        \Config::set('webmonksblog.captcha.basic_answers', "answer1,answer2");

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


        \Config::set('webmonksblog.comments.type_of_comments_to_show', 'built_in');


        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            $captcha->captcha_field_name() => "wronganswer1", // << WRONG CAPTCHA
        ];
        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config("webmonksblog.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);


        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            // << NO CAPTCHA FIELD
        ];
        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config("webmonksblog.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);


    }


    public function testCreatePostThenSetCommentsToDisabledAndCheckNoneShow()
    {


        \Config::set('webmonksblog.comments.type_of_comments_to_show', "disabled");

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new \WebMonksBlog\Models\WebMonksPost;

        $newblogpost->title=__METHOD__ . " " . time();


        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        foreach($new_object_vals as $k=>$v) {
            $newblogpost->$k=$v;
        }
        $newblogpost->save();


        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertDontSee("var disqus_config");
        $response->assertDontSee("Add a comment");
        $response->assertDontSee("Captcha");
        $response->assertDontSee("maincommentscontainer");
        $response->assertDontSee("Comments");
        $response->assertDontSee("You must customise this by creating a file");
    }
    public function testCreatePostThenSetCommentsToDisqusAndCheckDisqusJSIsShown()
    {
        \Config::set('webmonksblog.comments.type_of_comments_to_show', "disqus");

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new \WebMonksBlog\Models\WebMonksPost;

        $newblogpost->title=__METHOD__ . " " . time();


        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        foreach($new_object_vals as $k=>$v) {
            $newblogpost->$k=$v;
        }
        $newblogpost->save();


        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertSee("var disqus_config");
    }

    public function testCreatePostThenSetCommentsToCustomAndCheckCustomErrorShows()
    {


        \Config::set('webmonksblog.comments.type_of_comments_to_show', "custom");

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        $newblogpost = new \WebMonksBlog\Models\WebMonksPost;

        $newblogpost->title=__METHOD__ . " " . time();


        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        foreach($new_object_vals as $k=>$v) {
            $newblogpost->$k=$v;
        }
        $newblogpost->save();


        $response = $this->get($newblogpost->url());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200); // redirect
        $response->assertSee("You must customise this by creating a file"); //config type of comments is set to show 'custom', which should (by default) show an error messaging telling user to customise the custom view file. If this is run on a site with its own file defined this will show an error (but it will not be a real error).;
    }


    public function testCreatePostThenCheckCanCreateCommentThenApproveCommentWithBasicCaptchaEnabled()
    {


        \Config::set('webmonksblog.comments.auto_approve_comments', false);
        \Config::set('webmonksblog.captcha.captcha_enabled', true);
        \Config::set('webmonksblog.captcha.captcha_type', \WebMonksBlog\Captcha\Basic::class);
        $captcha = new \WebMonksBlog\Captcha\Basic();
        \Config::set('webmonksblog.captcha.basic_question', "a test question");
        \Config::set('webmonksblog.captcha.basic_answers', "answer1,answer2");

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


        \Config::set('webmonksblog.comments.type_of_comments_to_show', 'built_in');


        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
            $captcha->captcha_field_name() => "AnsWer2",
        ];
        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config("webmonksblog.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
        $response->assertStatus(200);

        \Config::set('webmonksblog.captcha.auto_approve_comments', false);

        $this->assertDatabaseHas('webmonks_comments', ['approved' => false, 'author_name' => $comment_detail['author_name']]);


        $justAddedRow = \WebMonksBlog\Models\WebMonksComment::withoutGlobalScopes()->where('author_name', $comment_detail['author_name'])->firstOrFail();

        $response = $this->get(route("webmonksblog.admin.comments.index"));
        $response->assertSee($justAddedRow->author_name);


        // approve it:
        $response = $this->patch(route("webmonksblog.admin.comments.approve", $justAddedRow->id), [
            '_token' => csrf_token(),
        ]);
        // check it was approved
        $response->assertStatus(302);
        $this->assertDatabaseHas('webmonks_comments', ['approved' => 1, 'author_name' => $justAddedRow->author_name]);


    }


    public function testCreatePostThenCheckCanCreateCommentThenApproveComment()
    {
        \Config::set('webmonksblog.comments.auto_approve_comments', false);
        \Config::set('webmonksblog.captcha.captcha_enabled', false);

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


        \Config::set('webmonksblog.comments.type_of_comments_to_show', 'built_in');
        \Config::set('webmonksblog.captcha.captcha_enabled', false);


        $comment_detail = [
            '_token' => csrf_token(),
            'author_name' => str_random(),
            'comment' => str_random(),
        ];
        $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);
        $response = $this->post(config("webmonksblog.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);

        $response->assertStatus(200);

        \Config::set('webmonksblog.captcha.auto_approve_comments', false);

        $this->assertDatabaseHas('webmonks_comments', ['approved' => false, 'author_name' => $comment_detail['author_name']]);


        $justAddedRow = \WebMonksBlog\Models\WebMonksComment::withoutGlobalScopes()->where('author_name', $comment_detail['author_name'])->firstOrFail();

        $response = $this->get(route("webmonksblog.admin.comments.index"));
        $response->assertSee($justAddedRow->author_name);


        // approve it:
        $response = $this->patch(route("webmonksblog.admin.comments.approve", $justAddedRow->id), [
            '_token' => csrf_token(),
        ]);
        // check it was approved
        $response->assertStatus(302);
        $this->assertDatabaseHas('webmonks_comments', ['approved' => 1, 'author_name' => $justAddedRow->author_name]);


    }


    public function testCreatePostThenCheckCanCreateCommentThenDeleteComment()
    {
        \Config::set('webmonksblog.comments.auto_approve_comments', false);
        \Config::set('webmonksblog.captcha.captcha_enabled', false);

        $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


        if (config("webmonksblog.comments.type_of_comments_to_show") === 'built_in') {
            $comment_detail = [
                '_token' => csrf_token(),
                'author_name' => str_random(),
                'comment' => str_random(),
            ];
            $this->assertDatabaseMissing('webmonks_comments', ['author_name' => $comment_detail['author_name']]);
            $response = $this->post(config("webmonksblog.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
            $response->assertSessionHasNoErrors();
            $response->assertStatus(200);

            $this->assertDatabaseHas('webmonks_comments', ['author_name' => $comment_detail['author_name']]);


            $justAddedRow = \WebMonksBlog\Models\WebMonksComment::withoutGlobalScopes()->where('author_name', $comment_detail['author_name'])->firstOrFail();

            // check the just added row exists...
            $response = $this->get(route("webmonksblog.admin.comments.index"));
            $response->assertSee($justAddedRow->author_name);


            // delete it:
            $response = $this->delete(route("webmonksblog.admin.comments.delete", $justAddedRow->id), [
                '_token' => csrf_token(),
            ]);
            // check it was deleted (it will deleted if approved)
            $response->assertStatus(302);

            //check it doesnt exist in database
            $this->assertDatabaseMissing('webmonks_comments', ['id' => $justAddedRow->id,]);


        } else {
            dump("NOT TESTING COMMENT FEATURE, as config(\"webmonksblog.comments.type_of_comments_to_show\") is not set to 'built_in')");
        }

    }


    public function testCanCreateThenDeletePost()
    {


        $user = $this->create_admin_user();

        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertSessionHasNoErrors();


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('web_monks_posts', $search_for_obj);


        $justCreatedRow = \WebMonksBlog\Models\WebMonksPost::where("slug", $new_object_vals['slug'])->firstOrFail();
        $id = $justCreatedRow->id;
        $delete_url = $admin_panel_url . "/delete_post/" . $id;

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);

    }


    public function testCanCreateCategory()
    {
        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('webmonks_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('webmonks_categories', $search_for_new_cat);


    }


    public function testCanCreateCategoryThenEditIt()
    {


        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");


        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];


        // create a post so we can edit it later
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('webmonks_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('webmonks_categories', $search_for_new_cat);


        // get the just inserted row
        $justCreatedRow = \WebMonksBlog\Models\WebMonksCategory::where("slug", $new_cat_vals['slug'])->firstOrFail();


        // get the edit page (form)
        $response = $this->get(
            $admin_panel_url . "/categories/edit_category/" . $justCreatedRow->id
        );
        $response->assertStatus(200);

        // create some edits...
        $new_object_vals['category_name'] = "New category name " . str_random();
        $new_object_vals['slug'] = $justCreatedRow->slug;
        $new_object_vals['_token'] = csrf_token();


        $this->assertDatabaseMissing('webmonks_categories', ['category_name' => $new_object_vals['category_name']]);


        // send the request to save the changes
        $response = $this->patch(
            route("webmonksblog.admin.categories.update_category", $justCreatedRow->id),
            $new_object_vals
        );


        $response->assertStatus(302); // check it was a redirect

        // check that the edited category name is in the database.
        $this->assertDatabaseHas('webmonks_categories', ['slug' => $new_object_vals['slug'], 'category_name' => $new_object_vals['category_name']]);


    }


    public function testCanDeleteCategory()
    {
        $admin_panel_url = config("webmonksblog.admin_prefix", "blog_admin");
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('webmonks_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('webmonks_categories', $search_for_new_cat);


        $justCreatedRow = \WebMonksBlog\Models\WebMonksCategory::where("slug", $new_cat_vals['slug'])->firstOrFail();
        $id = $justCreatedRow->id;

        $delete_url = $admin_panel_url . "/categories/delete_category/$id";

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('webmonks_categories', $search_for_new_cat);

    }

    /**
     * @return array
     */
    protected function generate_basic_blog_post_with_random_data()
    {
        $new_object_vals = [];

        foreach ([
                     'title',
                     'subtitle',
                     'slug',
                     'post_body',
                     'meta_desc',
                 ] as $field) {
            $new_object_vals[$field] = str_random();
        }
        return $new_object_vals;
    }

    /**
     * @return mixed
     */
    protected function create_admin_user()
    {


        $user = $this->getMockBuilder(\App\User::class)
            ->getMock();
        // make sure the user can see admin panel
        $user->method("canManageWebMonksBlogPosts")
            ->will($this->returnCallback(function () {
                return true;
            }));


        // set up some dummy info
        $user->name = str_random() . "testuser";
        $user->password = str_random();
        $user->email = str_random() . "@example.com";

        $this->actingAs($user);


        //get a page (for session to be set/ for csrf) - do not delete this line! We don't need to do anything with the response though
        $this->get("/");


        return $user;
    }

    /**
     * @return array
     */
    protected function prepare_post_creation()
    {
        $user = $this->create_admin_user();

        $this->assertTrue($user->canManageWebMonksBlogPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
//        $new_object_vals['use_view_file'] = "";

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('web_monks_posts', $search_for_obj);

        // check we don't see it at moment
        $response = $this->get(config("webmonksblog.blog_prefix", "blog"));
        $response->assertDontSee($new_object_vals['slug']);
        return array($new_object_vals, $search_for_obj);
    }

    public function testUserModelHasCanManageWebMonksBlogPostsMethod()
    {
        $u = new \App\User();
        $this->assertTrue(method_exists($u,"canManageWebMonksBlogPosts"),"canManageWebMonksBlogPosts() must be added to User model. Please see webmonks WebMonksBlog docs for details. It should return true ONLY for the admin users");
    }
    public function testUserModelCanManageWebMonksBlogPostsMethodDoesntAlwaysReturnTrue()
    {
        $u = new \App\User();

        $u->id = 9999999; // in case the logic on canManageWebMonksBlogPosts() checks for a low ID
        $u->email = str_random(); // in case the logic looks for a certain email or something.

        $this->assertTrue(method_exists($u,"canManageWebMonksBlogPosts"));

        // because this user is just a randomly made one, it probably should not be allowed to edit blog posts.
        $this->assertFalse($u->canManageWebMonksBlogPosts(), "User::canManageWebMonksBlogPosts() returns true, but it PROBABLY should return false! Otherwise every single user on your site has access to the blog admin panel! This might not be an error though, if your system doesn't allow public registration. But you should look into this. I know this isn't a good way to handle this test, but it seems to make sense.");
    }


}

