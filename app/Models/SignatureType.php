<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureType extends Model
{
    use HasFactory;
    protected $table = 'mer_sign_type', $guarded = ['st_id'];
    public $timestamps = false;
    protected $primaryKey = 'st_id';
}
