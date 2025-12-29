<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipwhitelisting extends Model
{
    use HasFactory;
    protected $fillable=[
        'company_name','ip_address'
    ];
}
