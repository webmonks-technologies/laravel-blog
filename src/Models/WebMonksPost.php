<?php

namespace WebMonksBlog\Models;

use Illuminate\Database\Eloquent\Model;
use WebMonksBlog\Scopes\WebMonksBlogPublishedScope;

/**
 * Class WebMonksPost
 * @package WebMonksBlog\Models
 */
class WebMonksPost extends Model
{
    /**
     * @var array
     */
    public $casts = [
        'is_published' => 'boolean',
        'posted_at' => 'date'
    ];

    /**
     * @var array
     */
    public $dates = [
        'posted_at'
    ];

    /**
     * @var array
     */
    public $fillable = [
        'is_published',
        'posted_at',
    ];

    /**
     * The associated post translations
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function postTranslations()
    {
        return $this->hasMany(WebMonksPostTranslation::class,"post_id");
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /* If user is logged in and \Auth::user()->canManageWebMonksBlogPosts() == true, show any/all posts.
           otherwise (which will be for most users) it should only show published posts that have a posted_at
           time <= Carbon::now(). This sets it up: */
        static::addGlobalScope(new WebMonksBlogPublishedScope());

        static::deleting(function($post) { // before delete() method call this
            $post->postTranslations()->delete();
        });
    }

    /**
     * The associated author (if user_id) is set
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(config("webmonksblog.user_model"), 'user_id');
    }

    /**
     * Return author string (either from the User (via ->user_id), or the submitted author_name value
     * @return string
     */
    public function author_string()
    {
        if ($this->author) {
            return optional($this->author)->name;
        } else {
            return 'Unknown Author';
        }
    }

    /**
     * The associated categories for this blog post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(WebMonksCategory::class, 'webmonks_post_categories','post_id','category_id');
    }

    /**
     * Comments for this post
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(WebMonksComment::class, 'post_id');
    }

}
