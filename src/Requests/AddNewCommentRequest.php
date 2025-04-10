<?php

namespace WebMonksBlog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddNewCommentRequest extends FormRequest
{

    public function authorize()
    {
        if (config("webmonksblog.comments.type_of_comments_to_show") === 'built_in') {
            // anyone is allowed to submit a comment, to return true always.
            return true;
        }

        //comments are disabled so just return false to disallow everyone.
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // basic rules
        $return = [
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
            'author_name' => ['string', 'min:1', 'max:50'],
            'author_email' => ['string', 'nullable', 'min:1', 'max:254', 'email'],
            'author_website' => ['string', 'nullable', 'min:' . strlen("http://a.b"), 'max:175', 'active_url',],
        ];

        // do we need author name?
        if (\Auth::check() && config("webmonksblog.comments.save_user_id_if_logged_in", true)) {
            // is logged in, so we don't need an author name (it won't get used)
            $return['author_name'][] = 'nullable';
        } else {
            // is a guest - so we require this
            $return['author_name'][] = 'required';
        }

        // is captcha enabled? If so, get the rules from its class.
        if (config("webmonksblog.captcha.captcha_enabled")) {
            /** @var string $captcha_class */
            $captcha_class = config("webmonksblog.captcha.captcha_type");

            /** @var \WebMonksBlog\Interfaces\CaptchaInterface $captcha */
            $captcha = new $captcha_class;

            $return[$captcha->captcha_field_name()] = $captcha->rules();
        }

        // in case you need to implement something custom, you can use this...
        if (config("webmonksblog.comments.rules") && is_callable(config("webmonksblog.comments.rules"))) {
            /** @var callable $func */
            $func = config('webmonksblog.comments.rules');
            $return = $func($return);
        }

        if (config("webmonksblog.comments.require_author_email")) {
            $return['author_email'][] = 'required';
        }

        return $return;
    }

}
