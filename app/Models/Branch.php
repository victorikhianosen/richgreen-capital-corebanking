<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable=[
        'branch_name','branch_code','address','default_branch'
    ];

    public function accountofficers(){
        return $this->hasMany(Accountofficer::class);
    }
    
    public function users(){
        return $this->hasMany(User::class, 'branch_users');
    }

    public function assets(){
        return $this->hasMany(Asset::class);
    }

    public function assetvalues(){
        return $this->hasMany(AssetValuation::class);
    }
    
    public function customers(){
        return $this->hasMany(Customer::class);
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

    public function otherincom(){
        return $this->hasMany(OtherIncome::class);
    }

    public function savingstran(){
        return $this->hasMany(SavingsTransaction::class);
    }
    
     public function savingstrangl(){
        return $this->hasMany(SavingsTransactionGL::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }
    
     public function uploadstrxstus(){
        return $this->hasMany(Upload_transaction_status::class);
    }
}
