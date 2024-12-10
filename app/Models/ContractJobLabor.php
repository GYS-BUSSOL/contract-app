<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJobLabor extends Model
{
    use HasFactory;

    protected $table = 'trn_contract_job_labor', $guarded = ['cjl_id'];
    public $timestamps = false;
    protected $primaryKey = 'cjl_id';
}
