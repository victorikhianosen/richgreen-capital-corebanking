<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubcriptionLog extends Model
{
    use HasFactory;

    protected $fillable =[
        'subcription','amount_paid','paymentref','vat','total_paid','expense_account','credit_account','warning_date',
        'expiration_date','payment_date','note','is_active','branch_id'
    ];
}
