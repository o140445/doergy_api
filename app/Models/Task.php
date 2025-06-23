<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{


    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'eisenhower_type',
        'due_date',
        'completed_at',
        'start_time',
        'end_time',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(PlatformUser::class, 'user_id');
    }

    public function labels()
    {
        return $this->belongsToMany(Labels::class, 'task_labels', 'task_id', 'label_id');
    }
}
