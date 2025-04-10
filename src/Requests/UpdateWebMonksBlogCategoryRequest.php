<?php

namespace WebMonksBlog\Requests;


use Illuminate\Validation\Rule;
use WebMonksBlog\Models\WebMonksCategory;

class UpdateWebMonksBlogCategoryRequest extends BaseWebMonksBlogCategoryRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = $this->baseCategoryRules();
        return $return;

    }
}
