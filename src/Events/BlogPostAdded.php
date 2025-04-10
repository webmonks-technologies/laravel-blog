<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksPost;

/**
 * Class BlogPostAdded
 * @package WebMonksBlog\Events
 */
class BlogPostAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksPost */
    public $webmonksBlogPost;

    /**
     * BlogPostAdded constructor.
     * @param WebMonksPost $webmonksBlogPost
     */
    public function __construct(WebMonksPost $webmonksBlogPost)
    {
        $this->webmonksBlogPost=$webmonksBlogPost;
    }

}
