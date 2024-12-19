<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectCategory extends Model
{
    use HasFactory;

    protected $table = 'mer_kategori_reject', $guarded = ['katrj_id'];
    public $timestamps = false;
    protected $primaryKey = 'katrj_id';
}
