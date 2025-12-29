<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingFee extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','name','savings_products','amount','fees_posting','fees_adding','new_fee_type'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    protected $casts = [
        'savings_products' => 'array',
    ];
}
