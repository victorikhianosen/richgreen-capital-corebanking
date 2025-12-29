<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsProduct extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','name','product_number','allow_overdraw','interest_rate','minimum_balance','interest_posting','interest_adding','notes'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function savings(){
        return $this->hasMany(Saving::class);
    }

}
