<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PBL extends Model
{
    use HasFactory;
    protected $table = 'trn_contract', $guarded = ['con_id'];
}