<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJobTarget extends Model
{
    use HasFactory;
    protected $table = 'trn_contract_job_target', $guarded = ['cjt_id'];
    public $timestamps = false;
    protected $primaryKey = 'cjt_id';
}
