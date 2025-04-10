<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksComment;
use WebMonksBlog\Models\WebMonksPost;

/**
 * Class CommentAdded
 * @package WebMonksBlog\Events
 */
class CommentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksPost */
    public $webmonksBlogPost;
    /** @var  WebMonksComment */
    public $newComment;

    /**
     * CommentAdded constructor.
     * @param WebMonksPost $webmonksBlogPost
     * @param WebMonksComment $newComment
     */
    public function __construct(WebMonksPost $webmonksBlogPost, WebMonksComment $newComment)
    {
        $this->webmonksBlogPost=$webmonksBlogPost;
        $this->newComment=$newComment;
    }

}
