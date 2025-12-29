<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentSchedule extends Model
{
    use HasFactory;

    protected $fillable=[
        'fixed_deposit_id','customer_id','branch_id','description','due_date','principal','interest','rollover','total_interest','total_due','closed','payment_date','payment_method','posted_by'
];

public function branch(){
    return $this->belongsTo(Branch::class, 'branch_id');
}

public function fixed_deposit(){
    return $this->belongsTo(FixedDeposit::class, 'fixed_deposit_id');
}

public function customer(){
    return $this->belongsTo(Customer::class,'customer_id');
}
}
