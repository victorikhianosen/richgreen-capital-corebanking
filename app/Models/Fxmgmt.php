<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fxmgmt extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','accountofficer_id','exchangerate_id','customer','purchase_exchange_rate','sales_exchange_rate','naria_amount',
        'foreign_amount','fee_amount','fx_reference','purchase_naria_from','purchase_recieve_currency','payment_mode','sales_from',
        'sales_paid_to','sales_margin','beneficiary','beneficiary_bank','depositor','actual_payment',
        'swift_bank_charges','description','fxtype','initiated_by','branch_id','tranx_date','rev_status'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }

    public function exchangerate(){
        return $this->belongsTo(Exchangerate::class,'exchangerate_id');
    }

}
