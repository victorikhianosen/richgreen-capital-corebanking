<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanFee extends Model
{
    use HasFactory;

    protected $fillable=[
        'name','loan_fee_type','gl_code'
    ];

    public function loanfeemetas(){
        return $this->hasMany(LoanFeeMeta::class,'loan_fee_id');
    }
}
