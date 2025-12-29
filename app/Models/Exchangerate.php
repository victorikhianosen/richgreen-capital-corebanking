<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchangerate extends Model
{
    use HasFactory;
    protected $fillable=[
        'currency','currency_rate','currency_symbol'
    ];

    public function fxmgts(){
        return $this->hasMany(Fxmgmt::class);
    }
    
    public function customer(){
        return $this->hasMany(Customer::class);
    }
}
