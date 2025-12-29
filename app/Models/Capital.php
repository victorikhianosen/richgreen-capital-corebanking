<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id','branch_id','share_holder_name','percentage','amount','notes'
    ];
}
