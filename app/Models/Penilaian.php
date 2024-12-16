<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;
    protected $table = 'trn_penilaian', $guarded = ['pn_id'];
    public $timestamps = false;
    protected $primaryKey = 'pn_id';
}
