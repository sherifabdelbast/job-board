<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{

 protected  $fillable = [
        'user_id',
        'phone',
        'resume',
        'bio',
        'skills',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function savedJobListings()
    {
        return $this->belongsToMany(JobListing::class, 'saved_jobs')->withTimestamps();
    }


}
