<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GreetSetting extends Model
{
    protected $table = 'greet_settings';
    protected $fillable = ['message'];
}
