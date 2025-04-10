<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksPost;

/**
 * Class BlogPostWillBeDeleted
 * @package WebMonksBlog\Events
 */
class BlogPostWillBeDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksPost */
    public $webmonksBlogPost;

    /**
     * BlogPostWillBeDeleted constructor.
     * @param WebMonksPost $webmonksBlogPost
     */
    public function __construct(WebMonksPost $webmonksBlogPost)
    {
        $this->webmonksBlogPost=$webmonksBlogPost;
    }

}
