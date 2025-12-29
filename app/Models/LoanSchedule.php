<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable=[
        'loan_id','customer_id','branch_id','description','due_date','principal','principal_balance','interest','fees','penalty','due',
        'system_generated','closed','missed','missed_penalty_applied'
];

public function branch(){
    return $this->belongsTo(Branch::class, 'branch_id');
}

public function loan(){
    return $this->belongsTo(Loan::class, 'loan_id');
}

public function customer(){
    return $this->belongsTo(Customer::class,'customer_id');
}

}
