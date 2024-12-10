<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJobType extends Model
{
    use HasFactory;

    protected $table = 'trn_contract_job_type', $guarded = ['cjtype_id'];
    public $timestamps = false;
    protected $primaryKey = 'cjtype_id';
}
