<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerVendor extends Model
{
    use HasFactory;

    protected $table = 'mer_vendor', $guarded = ['vnd_id'];
}
