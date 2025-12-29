<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable=[
        'loan_id','accountofficer_id','customer_id','branch_id','user_id','amount','repayment_method','reference','collection_date','notes','type','due_date','status','created_by','updated_by'
    ];

    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loan(){
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
    
     public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }
}
