<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmetRepayment extends Model
{
    use HasFactory;
    protected $fillable=[
        'fixed_deposit_id','accountofficer_id','customer_id','branch_id','user_id','amount','payment_method','collection_date','notes','due_date'
    ];

    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fixed_deposit(){
        return $this->belongsTo(FixedDeposit::class, 'fixed_deposit_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
    
     public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }
}
