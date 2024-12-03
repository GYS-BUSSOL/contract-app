<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementUnit extends Model
{
    use HasFactory;
    protected $table = 'mer_measurement_unit', $guarded = ['unt_id'];
    public $timestamps = false;
    protected $primaryKey = 'unt_id';
}
