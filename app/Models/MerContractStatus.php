<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerContractStatus extends Model
{
    use HasFactory;

    protected $table = 'mer_contract_status', $guarded = ['sts_id'];
}
