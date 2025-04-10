<?php

namespace WebMonksBlog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use WebMonksBlog\Interfaces\BaseRequestInterface;

/**
 * Class BaseRequest
 * @package WebMonksBlog\Requests
 */
abstract class BaseRequest extends FormRequest implements BaseRequestInterface
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check() && \Auth::user()->canManageWebMonksBlogPosts();
    }
}
