<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTemplate extends Model
{
    use HasFactory;
    protected $table = 'mer_payment_template', $guarded = ['id'];
    public $timestamps = false;
    protected $primaryKey = 'id';
}
