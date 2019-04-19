<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['title', 'description', 'state_id', 'user_id', 'start_date', 'close_date'];
    //
    public function tasks(){
        return $this->hasMany(Task::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function state(){
        return $this->belongsTo(State::class);
    }
}
