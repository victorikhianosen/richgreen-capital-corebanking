<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentStructure extends Model
{
    use HasFactory;

    protected $fillable=[
        'payroll_id','branch_id','basic','other_allowance','gross_pay','paye_percent','paye','other_deduction','deduction','net_pay'
     ];

    public function payroll(){
        return $this->belongsTo(Payroll::class,'payroll_id');
    }

    public function payslips(){
        return $this->hasMany(Payslip::class);
    }
}
