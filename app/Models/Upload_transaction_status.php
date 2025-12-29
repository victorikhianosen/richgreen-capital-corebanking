<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload_transaction_status extends Model
{
    use HasFactory;
    protected $fillable=[
        'branch_id','customer_id','general_ledger_id','balance','amount','trx_type','gl_type','trx_date','reason','trx_status','upload_status'
    ];
    
     public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    public function general_ledger(){
        return $this->belongsTo(GeneralLedger::class, 'general_ledger_id');
    }
    
    public function getgeneralledger($id){
        $glcde = GeneralLedger::select('gl_name','gl_code')->where('id',$id)->first();
        return  $glcde ? $glcde->gl_name." --".$glcde->gl_code : "N/A";
    }
}
