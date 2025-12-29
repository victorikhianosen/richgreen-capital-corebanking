<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','branch_id','other_income_type_id','amount','income_date','notes','files'
    ];

    public function otherincomtype(){
        return $this->belongsTo(OtherIncomeType::class,'other_income_type_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
