<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','name','gl_code','interest_gl','incomefee_gl','loan_disbursed_by','minimum_principal','default_principal','maximum_principal','interest_method','interest_rate',
        'interest_period','minimum_interest_rate','default_interest_rate','maximum_interest_rate','override_interest','override_interest_amount',
        'default_loan_duration','default_loan_duration_type','repayment_cycle','repayment_order','loan_fees_schedule','branch_access','grace_on_interest_charged',
        'advanced_enabled','enable_late_repayment_penalty','enable_after_maturity_date_penalty','after_maturity_date_penalty_type','late_repayment_penalty_type',
        'late_repayment_penalty_calculate','after_maturity_date_penalty_calculate','late_repayment_penalty_amount','after_maturity_date_penalty_amount','late_repayment_penalty_grace_period',
        'after_maturity_date_penalty_grace_period','late_repayment_penalty_recurring','after_maturity_date_penalty_recurring'
    ];

    protected $casts =[
        'loan_disbursed_by' => 'array',
        'repayment_order' => 'array'
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }

    public function loanfeemetas(){
        return $this->hasMany(LoanFeeMeta::class);
    }
}
