<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsTransactionGL extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','branch_id','general_ledger_id','amount','type','device','slip','reference_no','notes','status','initiated_by','approved_by','approve_date'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function generalledger(){
        return $this->belongsTo(GeneralLedger::class,'general_ledger_id');
    }
}
