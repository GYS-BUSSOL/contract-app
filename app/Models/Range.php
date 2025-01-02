<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Range extends Model
{
    use HasFactory;
    protected $table = 'trn_range', $guarded = ['id'];
    public $timestamps = false;
    protected $primaryKey = 'id';
}
