<?php

namespace WebMonksBlog\Models;

use Illuminate\Database\Eloquent\Model;

class WebMonksUploadedPhoto extends Model
{
    public $table = 'webmonks_uploaded_photos';
    public $casts = [
        'uploaded_images' => 'array',
    ];
    public $fillable = [

        'image_title',
        'uploader_id',
        'source', 'uploaded_images',
    ];
}
