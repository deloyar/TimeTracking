<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Effort extends Model
{
    protected $fillable = ['user_id', 'task_id', 'effort_hours', 'date'];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function task(){
        return $this->belongsTo(Task::class);
    }
}
