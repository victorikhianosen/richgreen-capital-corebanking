<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    use HasFactory;
    protected $fillable=[
        'name','type','description'
     ];
     
     public function generalledger(){
          return $this->hasMany(GeneralLedger::class,'account_category_id');
    }
}
