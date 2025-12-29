<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable=[
        'customer_id','account_name','account_number','bank_name','bank_code','bank_shortcode','type'
    ];
}
