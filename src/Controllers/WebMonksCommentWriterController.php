<?php

namespace WebMonksBlog\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use WebMonksBlog\Captcha\CaptchaAbstract;
use WebMonksBlog\Captcha\UsesCaptcha;
use WebMonksBlog\Events\CommentAdded;
use WebMonksBlog\Middleware\LoadLanguage;
use WebMonksBlog\Middleware\UserCanManageBlogPosts;
use WebMonksBlog\Models\WebMonksComment;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Models\WebMonksPostTranslation;
use WebMonksBlog\Requests\AddNewCommentRequest;

/**
 * Class WebMonksCommentWriterController
 * @package WebMonksBlog\Controllers
 */
class WebMonksCommentWriterController extends Controller
{

    use UsesCaptcha;

    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
        $this->middleware(LoadLanguage::class);

    }

    /**
     * Let a guest (or logged in user) submit a new comment for a blog post
     *
     * @param AddNewCommentRequest $request
     * @param $blog_post_slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function addNewComment(AddNewCommentRequest $request, $locale, $blog_post_slug)
    {

        if (config("webmonksblog.comments.type_of_comments_to_show", "built_in") !== 'built_in') {
            throw new \RuntimeException("Built in comments are disabled");
        }

        $post_translation = WebMonksPostTranslation::where("slug", $blog_post_slug)
            ->with('post')
            ->firstOrFail();
        $blog_post = $post_translation->post;

        /** @var CaptchaAbstract $captcha */
        $captcha = $this->getCaptchaObject();
        if ($captcha) {
            $captcha->runCaptchaBeforeAddingComment($request, $blog_post);
        }

        $new_comment = $this->createNewComment($request, $blog_post);

        return view("webmonksblog::saved_comment", [
            'captcha' => $captcha,
            'blog_post' => $post_translation,
            'new_comment' => $new_comment
        ]);

    }

    /**
     * @param AddNewCommentRequest $request
     * @param $blog_post
     * @return WebMonksComment
     */
    protected function createNewComment(AddNewCommentRequest $request, $blog_post)
    {
        $new_comment = new WebMonksComment($request->all());

        if (config("webmonksblog.comments.save_ip_address")) {
            $new_comment->ip = $request->ip();
        }
        if (config("webmonksblog.comments.ask_for_author_website")) {
            $new_comment->author_website = $request->get('author_website');
        }
        if (config("webmonksblog.comments.ask_for_author_email")) {
            $new_comment->author_email = $request->get('author_email');
        }
        if (config("webmonksblog.comments.save_user_id_if_logged_in", true) && Auth::check()) {
            $new_comment->user_id = Auth::user()->id;
        }

        $new_comment->approved = config("webmonksblog.comments.auto_approve_comments", true) ? true : false;

        $blog_post->comments()->save($new_comment);

        event(new CommentAdded($blog_post, $new_comment));

        return $new_comment;
    }

}
