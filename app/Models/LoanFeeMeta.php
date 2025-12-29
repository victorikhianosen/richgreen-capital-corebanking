<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanFeeMeta extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','parent_id','loan_fee_id','category','value','loan_fees_schedule'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loanfee(){
        return $this->belongsTo(LoanFee::class, 'loan_fee_id');
    }

    public function loan(){
        return $this->belongsTo(Loan::class, 'parent_id');
    }

    public function loanproduct(){
        return $this->belongsTo(LoanProduct::class, 'parent_id');
    }
}
