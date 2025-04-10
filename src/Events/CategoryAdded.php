<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksCategory;
use WebMonksBlog\Models\WebMonksCategoryTranslation;

/**
 * Class CategoryAdded
 * @package WebMonksBlog\Events
 */
class CategoryAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksCategory */
    public $webmonksCategory;
    public $webmonksCategoryTranslation;

    /**
     * CategoryAdded constructor.
     * @param WebMonksCategory $webmonksCategory
     */
    public function __construct(WebMonksCategory $webmonksCategory, WebMonksCategoryTranslation  $webmonksCategoryTranslation)
    {
        $this->webmonksCategory=$webmonksCategory;
        $this->webmonksCategoryTranslation = $webmonksCategoryTranslation;
    }

}
