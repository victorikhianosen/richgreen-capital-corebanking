<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    use HasFactory;

    protected $fillable=[
        'loan_id','customer_id','collateral_type_id','name','value','date','status','notes','photo','files',
        'serial_number','model_name','model_number','manufacture_date'
    ];

    protected $casts=[
        'files' => 'array'
    ];
    
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function loan(){
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function collateraltype(){
        return $this->belongsTo(CollateralType::class,'collateral_type_id');
    }
}
