<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborType extends Model
{
    use HasFactory;

    protected $table = 'mer_labor_type', $guarded = ['id'];
}
