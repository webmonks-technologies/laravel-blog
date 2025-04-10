<?php

namespace WebMonksBlog\Requests;

use Illuminate\Validation\Rule;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Requests\Traits\HasCategoriesTrait;
use WebMonksBlog\Requests\Traits\HasImageUploadTrait;

class UpdateWebMonksBlogPostRequest extends BaseWebMonksBlogPostRequest
{
    use HasCategoriesTrait;
    use HasImageUploadTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = $this->baseBlogPostRules();
        //        $return['slug'] [] = Rule::unique("web_monks_post_translations", "slug")->ignore($this->route()->parameter("blogPostId"));
        return $return;
    }
}
