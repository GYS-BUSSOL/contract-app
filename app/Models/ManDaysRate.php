<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManDaysRate extends Model
{
    use HasFactory;

    protected $table = 'mer_rate_tk', $guarded = ['rtk_id'];
    public $timestamps = false;
    protected $primaryKey = 'rtk_id';
}
