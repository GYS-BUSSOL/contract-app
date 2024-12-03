<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrnBUCCWC extends Model
{
    use HasFactory;

    protected $table = 'trn_bu_cc_wc', $guarded = ['tbc_id'];
    public $timestamps = false;
    protected $primaryKey = 'tbc_id';
}
