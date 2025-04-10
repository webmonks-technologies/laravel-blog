<?php

namespace WebMonksBlog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Models\WebMonksPostTranslation;

/**
 * Class UploadedImage
 * @package WebMonksBlog\Events
 */
class UploadedImage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var  WebMonksPost|null */
    public $webmonksBlogPost;
    /**
     * @var
     */
    public $image;

    public $source;
    public $image_filename;

    /**
     * UploadedImage constructor.
     *
     * @param $image_filename - the new filename
     * @param WebMonksPost $webmonksBlogPost
     * @param $image
     * @param $source string|null  the __METHOD__  firing this event (or other string)
     */
    public function __construct(string $image_filename, $image, WebMonksPostTranslation $webmonksBlogPost=null, string $source='other')
    {
        $this->image_filename = $image_filename;
        $this->webmonksBlogPost=$webmonksBlogPost;
        $this->image=$image;
        $this->source=$source;
    }

}
