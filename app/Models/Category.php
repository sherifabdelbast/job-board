<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];
    
public function jobListings()
    {
        return $this->belongsToMany(JobListing::class, 'category_job_listing')->withTimestamps();
    }

    
}
