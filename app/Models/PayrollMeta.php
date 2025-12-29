<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollMeta extends Model
{
    use HasFactory;

    protected $fillable=[
        'payroll_id','payroll_template_id','value','position'
    ];

    public function payroll(){
        return $this->belongsTo(Payroll::class,'payroll_id');
    }
    
    public function payrolltempl(){
        return $this->belongsTo(PayrollTemplate::class,'payroll_template_id');
    }
}
