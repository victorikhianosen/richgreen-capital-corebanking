<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedDeposit extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','customer_id','fixed_deposit_product_id','accountofficer_id','approved_by_id','declined_by_id',
       'closed_by_id','fixed_deposit_code','reference','old_disbursedate','old_maturedate','release_date',
        'maturity_date','interest_start_date','first_payment_date','principal','interest_method','interest_rate','interest_period','duration','duration_type','payment_cycle','status_id','description','fd_status',
        'balance','applied_amount','approved_amount','approved_notes','enable_withholding_tax','withholding_tax','closed_notes','declined_notes',
        'approved_date','closed_date','auto_book_investment','declined_date','status','system_approve','deleted_at','branch_id'
    ];


    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function fixed_deposit_product(){
        return $this->belongsTo(FixedDepositProduct::class,'fixed_deposit_product_id');
    }

    public function fd_approved(){
        return $this->belongsTo(User::class,'approved_by_id');
    }

    public function fd_disbursed(){
        return $this->belongsTo(User::class,'disbursed_by_id');
    }

    public function fd_withdrawn(){
        return $this->belongsTo(User::class,'withdrawn_by_id');
    }

    public function fd_declined(){
        return $this->belongsTo(User::class,'declined_by_id');
    }

    public function fd_writtenoff(){
        return $this->belongsTo(User::class,'written_off_by_id');
    }

    public function fd_rescheduled(){
        return $this->belongsTo(User::class,'rescheduled_by_id');
    }

    public function fd_closed(){
        return $this->belongsTo(User::class,'closed_by_id');
    }

    public function schedules()
    {
        return $this->hasMany(InvestmentSchedule::class);
    }

    public function repayments(){
        return $this->hasMany(InvestmetRepayment::class);
    }
}
