<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsTransaction extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','customer_id','branch_id','amount','type','device','system_interest','slip','is_approve','cust_int',
        'transfer_type','destination_account','reference_no','notes','status','status_type','trnx_type','initiated_by','approve_by','approve_date'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
    
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
