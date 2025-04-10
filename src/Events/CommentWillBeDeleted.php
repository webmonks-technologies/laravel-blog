<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksComment;

/**
 * Class CommentWillBeDeleted
 * @package WebMonksBlog\Events
 */
class CommentWillBeDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksComment */
    public $comment;

    /**
     * CommentWillBeDeleted constructor.
     * @param WebMonksComment $comment
     */
    public function __construct(WebMonksComment $comment)
    {
        $this->comment=$comment;
    }

}
