<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetHistory extends Model
{
    use HasFactory;

    protected $table = 'trn_budget_history', $guarded = ['bgth_id'];
    public $timestamps = false;
    protected $primaryKey = 'bgth_id';
}
