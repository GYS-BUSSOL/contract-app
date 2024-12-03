<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerBUCCWC extends Model
{
    use HasFactory;

    protected $table = 'mer_bu_cc_wc', $guarded = ['id'];
    public $timestamps = false;
}
