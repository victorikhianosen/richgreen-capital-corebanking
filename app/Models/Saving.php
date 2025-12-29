<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','customer_id','savings_product_id','account_balance','ledger_balance','lien_amount','lien_deducted','lien_remaining'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function savingprod(){
        return $this->belongsTo(SavingsProduct::class,'savings_product_id');
    }
}
