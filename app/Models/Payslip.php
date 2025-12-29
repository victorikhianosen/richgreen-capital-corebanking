<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;
    protected $fillable=[
        'payroll_id','payment_structure_id','branch_id','month','year'
     ];

    public function payroll(){
        return $this->belongsTo(Payroll::class,'payroll_id');
    }

    public function paymentstructure(){
        return $this->belongsTo(PaymentStructure::class,'payment_structure_id');
    }
}
