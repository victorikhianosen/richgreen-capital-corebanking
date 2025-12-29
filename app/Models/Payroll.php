<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','branch_id','employee_name','email','designation','payment_method','bank_name','account_number'
     ];

     public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment_structures(){
        return $this->hasMany(PaymentStructure::class);
    }

    public function payslips(){
        return $this->hasMany(Payslip::class);
    }
}
