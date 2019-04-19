<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['project_id', 'title', 'description', 'state_id', 'user_id', 'effort', 'remain_effort', 'completed_effort', 'start_date', 'end_date'];
    public function project(){
        return $this->belongsTo(Project::class);
    }
    public function state(){
        return $this->belongsTo(State::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function efforts(){
        return $this->hasMany(Effort::class);
    }
}
