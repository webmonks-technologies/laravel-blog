<?php namespace WebMonksBlog\Captcha;

use Illuminate\Http\Request;
use WebMonksBlog\Interfaces\CaptchaInterface;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Models\WebMonksPostTranslation;

abstract class CaptchaAbstract implements CaptchaInterface
{


    /**
     * executed when viewing single post
     *
     * @param Request $request
     * @param WebMonksPostTranslation $webmonksBlogPost
     *
     * @return void
     */
    public function runCaptchaBeforeShowingPosts(Request $request, WebMonksPostTranslation $webmonksBlogPost)
    {
        // no code here to run! Maybe in your subclass you can make use of this?
        /*

        But you could put something like this -
        $some_question = ...
        $correct_captcha = ...
        \View::share("correct_captcha",$some_question); // << reference this in the view file.
        \Session::put("correct_captcha",$correct_captcha);


        then in the validation rules you can check if the submitted value matched the above value. You will have to implement this.

        */
    }

    /**
     * executed when posting new comment
     *
     * @param Request $request
     * @param WebMonksPost $webmonksBlogPost
     *
     * @return void
     */
    public function runCaptchaBeforeAddingComment(Request $request, WebMonksPost $webmonksBlogPost)
    {
        // no code here to run! Maybe in your subclass you can make use of this?
    }

}
