<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPayload extends Model
{
    use HasFactory;
     protected $fillable = ['body','branch_id']; 
}
