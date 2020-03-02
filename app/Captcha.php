<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Captcha extends Model
{
    protected $fillable = [
      'code' , 'used' , 'auth_token' , 'user_id' , 'created_at'
    ];
}
