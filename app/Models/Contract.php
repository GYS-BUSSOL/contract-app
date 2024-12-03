<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'trn_contract', $guarded = ['con_id'];
    public $timestamps = false;
    protected $primaryKey = 'con_id';
}
