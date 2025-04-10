<?php


namespace WebMonksBlog\Models;

use Illuminate\Database\Eloquent\Model;


/**
 *
 */
class WebMonksConfiguration extends Model
{
    protected $primaryKey = 'key';

    public $fillable = [
        'key',
        'value'
    ];

    public static function get($key){
        $obj = WebMonksConfiguration::where('key', $key)->first();
        if ($obj){
            return $obj->value;
        }
        else{
            return null;
        }
    }

    public static function set($key, $value){
        $config = new WebMonksConfiguration();
        $config->key = $key;
        $config->value = $value;
        $config->save();
    }
}
