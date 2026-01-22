<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_logo',
        'company_website',
        'company_description',
        'location',
    ];
    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
}
