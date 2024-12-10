<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJob extends Model
{
    use HasFactory;

    protected $table = 'trn_contract_job', $guarded = [''];
    public $timestamps = false;
}
