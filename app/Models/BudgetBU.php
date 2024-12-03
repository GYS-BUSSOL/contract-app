<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetBU extends Model
{
    use HasFactory;

    protected $table = 'trn_budget', $guarded = ['bgt_id'];
}
