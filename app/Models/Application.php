<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

 protected $fillable = [
     'candidate_id',
     'job_listing_id',
     'cover_letter',
     'status',
 ];
 
 public function candidate()
 {
     return $this->belongsTo(Candidate::class);
 }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }


}
