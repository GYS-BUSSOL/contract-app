<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerShift extends Model
{
    use HasFactory;
    protected $table = 'mer_shift', $guarded = ['shift_id'];
    public $timestamps = false;
    protected $primaryKey = 'shift_id';
}
