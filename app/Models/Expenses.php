<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','expense_type_id','branch_id','amount','expense_account','credit_account','expslip','date','month','year','recurring','recur_frequency',
        'recur_start_date','recur_end_date','recur_next_date','recur_type','note','file'
    ];

    public function expensetype(){
        return $this->belongsTo(ExpenseType::class,'expense_type_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
