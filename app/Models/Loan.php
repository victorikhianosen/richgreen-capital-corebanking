<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','customer_id','branch_id','loan_product_id','accountofficer_id','approved_by_id','disbursed_by_id','withdrawn_by_id','declined_by_id',
        'written_off_by_id','rescheduled_by_id','closed_by_id','loan_code','reference','equity','purpose','old_disbursedate','old_maturedate','release_date',
        'maturity_date','interest_start_date','first_payment_date','principal','interest_method','interest_rate','interest_period','override_interest','override_interest_amount',
        'loan_duration','loan_duration_type','repayment_cycle','repayment_order','loan_fees_schedule','grace_on_interest_charged','loan_status_id','files','description','loan_status',
        'balance','override','applied_amount','approved_amount','approved_notes','disbursed_notes','withdrawn_notes','closed_notes','rescheduled_notes','declined_notes','written_off_notes',
        'approved_date','disbursed_by','disbursed_date','withdrawn_date','closed_date','rescheduled_date','declined_date','written_off_date','processing_fee','status','provision_date','provision_amount',
        'provision_type','sector_id','deleted_at'
    ];
    
    protected $casts = [
        'files' => 'array'
    ];
    
    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
    public function sector(){
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function loan_product(){
        return $this->belongsTo(LoanProduct::class,'loan_product_id');
    }

    public function loan_approved(){
        return $this->belongsTo(User::class,'approved_by_id');
    }

    public function loan_disbursed(){
        return $this->belongsTo(User::class,'disbursed_by_id');
    }

    public function loan_withdrawn(){
        return $this->belongsTo(User::class,'withdrawn_by_id');
    }

    public function loan_declined(){
        return $this->belongsTo(User::class,'declined_by_id');
    }

    public function loan_writtenoff(){
        return $this->belongsTo(User::class,'written_off_by_id');
    }

    public function loan_rescheduled(){
        return $this->belongsTo(User::class,'rescheduled_by_id');
    }

    public function loan_closed(){
        return $this->belongsTo(User::class,'closed_by_id');
    }

    public function loanfeemetas(){
        return $this->hasMany(LoanFeeMeta::class);
    }

    public function schedules()
    {
        return $this->hasMany(LoanSchedule::class);
    }

    public function collaterals(){
        return $this->hasMany(Collateral::class);
    }

    public function comments(){
        return $this->hasMany(LoanComment::class);
    }
    public function repayments(){
        return $this->hasMany(LoanRepayment::class);
    }
}
