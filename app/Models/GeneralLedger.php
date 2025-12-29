<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralLedger extends Model
{
    use HasFactory;

    protected $fillable=[
        'account_category_id','user_id','branch_id','gl_name','gl_code','gl_type','currency_id','status'
    ];

    public function savingstrangl(){
        return $this->hasMany(SavingsTransactionGL::class);
    }
    
    public function accountcategories(){
        return $this->belongsTo(AccountCategory::class,'account_category_id');
    }

    public function uploadstrxstus(){
        return $this->hasMany(Upload_transaction_status::class);
    }
    
     public function fxmgts(){
        return $this->hasMany(Fxmgmt::class);
    }
}
