<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'gender',
        'address',
        'phone',
        'city',
        'notes',
        'role_id',
        'branch_id',
        'last_login',
        'is_2fa_enable',
        'two_factor_code',
        'two_factor_expire_at',
        'password',
        'account_type',
        'status',
        'signature'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function role(){
    //     return $this->belongsTo(Role::class,'role_id');
    //  }
    public function loanfeemetas(){
        return $this->hasMany(LoanFeeMeta::class);
    }

     public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function assets(){
        return $this->hasMany(Asset::class);
    }

    public function assetvalues(){
        return $this->hasMany(AssetValuation::class);
    }
    
    public function emails(){
        return $this->hasMany(Email::class);
    }
    
    public function auditt(){
        return $this->hasMany(Audittrail::class);
    }

    public function expenses(){
        return $this->hasMany(Expenses::class);
    }

    public function otherincome(){
        return $this->hasMany(OtherIncome::class);
    }
    
    public function customers(){
        return $this->hasMany(Customer::class);
    }
    
    public function savingsproduct(){
        return $this->hasMany(SavingsProduct::class);
    }

    public function savings(){
        return $this->hasMany(Saving::class);
    }

    public function savingsfee(){
        return $this->hasMany(SavingFee::class);
    }

    public function savingstran(){
        return $this->hasMany(SavingsTransaction::class);
    }
    
     public function savingstrangl(){
        return $this->hasMany(SavingsTransactionGL::class);
    }
    
    public function loanproducts(){
        return $this->hasMany(LoanProduct::class);
    }
    
     public function fxmgts(){
        return $this->hasMany(Fxmgmt::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }
    
    public function comments(){
        return $this->hasMany(LoanComment::class);
    }
    
    public function repayments(){
        return $this->hasMany(LoanRepayment::class);
    }
    
    public function capitals(){
        return $this->hasMany(Capital::class);
    }
}
