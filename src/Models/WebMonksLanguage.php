<?php


namespace WebMonksBlog\Models;

use Illuminate\Database\Eloquent\Model;

class WebMonksLanguage extends Model
{
    public $fillable = [
        'name',
        'locale',
        'iso_code',
        'date_format',
        'active'
    ];


    /**
     * The associated post (if post_id) is set
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(WebMonksPost::class, 'post_id');
    }

    /**
     * The associated author (if category_id) is set
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(WebMonksCategory::class, 'category_id');
    }

}