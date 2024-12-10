<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPK extends Model
{
    use HasFactory;

    protected $table = 'trn_spk', $guarded = ['spk_id'];
    public $timestamps = false;
    protected $primaryKey = 'spk_id';
}
