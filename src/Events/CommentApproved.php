<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksComment;

/**
 * Class CommentApproved
 * @package WebMonksBlog\Events
 */
class CommentApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksComment */
    public $comment;

    /**
     * CommentApproved constructor.
     * @param WebMonksComment $comment
     */
    public function __construct(WebMonksComment $comment)
    {
        $this->comment=$comment;
        // you can get the blog post via $comment->post
    }

}
