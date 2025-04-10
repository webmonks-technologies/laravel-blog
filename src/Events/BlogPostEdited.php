<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksPost;

/**
 * Class BlogPostEdited
 * @package WebMonksBlog\Events
 */
class BlogPostEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksPost */
    public $webmonksBlogPost;

    /**
     * BlogPostEdited constructor.
     * @param WebMonksPost $webmonksBlogPost
     */
    public function __construct(WebMonksPost $webmonksBlogPost)
    {
        $this->webmonksBlogPost=$webmonksBlogPost;
    }

}
