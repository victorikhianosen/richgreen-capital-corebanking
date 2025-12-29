<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accountofficer extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','branch_id','full_name','email','gender','phone','address'
    ];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function customers(){
        return $this->hasMany(Customer::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }
    
     public function fixed_deposits(){
        return $this->hasMany(FixedDeposit::class);
    }

    public function fxmgts(){
        return $this->hasMany(Fxmgmt::class);
    }
    
    public function loanrepayments(){
        return $this->hasMany(LoanRepayment::class);
    }
}
