<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksCategory;

/**
 * Class CategoryEdited
 * @package WebMonksBlog\Events
 */
class CategoryEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksCategory */
    public $webmonksBlogCategory;

    /**
     * CategoryEdited constructor.
     * @param WebMonksCategory $webmonksBlogCategory
     */
    public function __construct(WebMonksCategory $webmonksBlogCategory)
    {
        $this->webmonksBlogCategory=$webmonksBlogCategory;
    }

}
