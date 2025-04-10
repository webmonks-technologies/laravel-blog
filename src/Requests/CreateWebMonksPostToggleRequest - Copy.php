<?php

namespace WebMonksBlog\Requests;


use Illuminate\Validation\Rule;
use WebMonksBlog\Requests\Traits\HasCategoriesTrait;
use WebMonksBlog\Requests\Traits\HasImageUploadTrait;

class CreateWebMonksPostToggleRequest extends BaseWebMonksBlogPostRequest
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
        //no rules
        return [];
    }
}
