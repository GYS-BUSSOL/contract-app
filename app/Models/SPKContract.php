<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPKContract extends Model
{
    use HasFactory;

    protected $table = 'trn_spk_contract', $guarded = ['spc_id'];
}
