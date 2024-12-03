<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'mer_payment_type', $guarded = ['paytype_id'];
    public $timestamps = false;
    protected $primaryKey = 'paytype_id';
}
