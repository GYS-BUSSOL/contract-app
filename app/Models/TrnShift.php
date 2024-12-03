<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrnShift extends Model
{
    use HasFactory;
    protected $table = 'trn_shift', $guarded = ['sh_id'];
    public $timestamps = false;
    protected $primaryKey = 'sh_id';
}
