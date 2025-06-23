<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $fillable = [
        'name',
//        'description',
//        'start_date',
//        'end_date',
//        'status',
        'user_id',
        'color',
        'is_favorite',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(PlatformUser::class, 'user_id');
    }
}
