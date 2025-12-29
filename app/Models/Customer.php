<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

     protected $guard='customer';

    protected $fillable=[
    'user_id','branch_id','accountofficer_id','title','first_name','last_name','email','phone','gender','section','religion','marital_status','residential_address','dob','country','state','state_lga',
    'account_type','exchangerate_id','whatsapp','ussd','acctno','refacct','bvn','nin','next_kin','kin_address','kin_phone','kin_relate','maiden','occupation','business_name','working_status','question','answer','enable_sms_alert','enable_sms_alert',
    'means_of_id','upload_id','photo','signature','username','password','pin','otp','otp_expiration_date','phone_verify','reg_date','failed_logins','transfer_limit','lien','failed_balance','failed_pin','source','status','referral_code','referral'
    ];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
     public function exrate(){
        return $this->belongsTo(Exchangerate::class,'exchangerate_id');
    }

    public function accountofficer(){
        return $this->belongsTo(Accountofficer::class,'accountofficer_id');
    }

    public function savings(){
        return $this->hasMany(Saving::class,'customer_id');
    }
    
    public function savingstran(){
        return $this->hasMany(SavingsTransaction::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class,'customer_id');
    }
    
    public function emails(){
        return $this->hasMany(Email::class,'user_id');
    }
    
    public function collaterals(){
        return $this->hasMany(Collateral::class);
    }
    
    public function repayments(){
        return $this->hasMany(LoanRepayment::class);
    }
    
     public function uploadstrxstus(){
        return $this->hasMany(Upload_transaction_status::class);
    }
}
