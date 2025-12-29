<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedDepositProduct extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','name','minimum_principal','default_principal','maximum_principal','interest_method','interest_rate',
        'interest_period','minimum_interest_rate','default_interest_rate','maximum_interest_rate','interest_payment','default_duration','default_duration_type','branch_id'
    ];

    protected $casts =[
        'repayment_order' => 'array'
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fixed_deposit(){
        return $this->hasMany(FixedDeposit::class);
    }
}
