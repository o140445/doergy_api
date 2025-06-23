<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Labels extends Model
{
    protected $fillable = [
        'name',
        'color',
        'user_id',
        'created_at',
        'updated_at',
    ];
}
