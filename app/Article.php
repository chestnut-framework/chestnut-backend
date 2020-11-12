<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //

    protected $dateFormat = "Y-m-d H:i:s";

    public function getCreatedAtAttribute()
    {
        return $this->attributes["created_at"];
    }

    public function publisher()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany('App\Like', 'likable');
    }

    // public function category()
    // {
    //     return $this->belongsTo('App\Category');
    // }
}
