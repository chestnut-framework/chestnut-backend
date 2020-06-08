<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'avatar',
    ];

    protected $hidden = [
        'id', 'user_id', 'created_at', 'updated_at',
    ];

    public function user()
    {
        $this->belongsTo('App\User');
    }
}
