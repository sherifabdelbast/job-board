<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'requirements',
        'location',
        'employment_type',
        'salary_min',
        'salary_max',
        'status',
        'expires_at',
    ];
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_job_listing')->withTimestamps();
    }

    public function candidatesWhoSaved()
    {
        return $this->belongsToMany(Candidate::class, 'saved_jobs')->withTimestamps();
    }


}
