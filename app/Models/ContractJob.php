<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJob extends Model
{
    use HasFactory;

    protected $table = 'trn_contract_job';
    protected $fillable = [
        'cjb_id',
        'con_id',
        'job_type_id',
        'cjb_desc',
        'cjb_pic',
        'cjb_qty',
        'unt_id',
        'cjb_pay_type',
        'cjb_pay_template',
        'cjb_rate',
        'cjb_transaction_status',
        'aud_delete',
        'aud_user',
        'aud_date',
        'aud_prog',
        'aud_machine'
    ];
    public $timestamps = false;
}
