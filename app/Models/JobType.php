<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use HasFactory;
    protected $table = 'mer_job_type', $guarded = ['job_type_id'];
    public $timestamps = false;
    protected $primaryKey = 'job_type_id';
}
