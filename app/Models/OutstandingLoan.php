<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutstandingLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id','customer_id','amount','branch_id'
    ];

     public function loan(){
        return $this->BelongsTo(Loan::class,'loan_id');
    }

    public function customer(){
        return $this->BelongsTo(Customer::class,'customer_id');
    }
}
