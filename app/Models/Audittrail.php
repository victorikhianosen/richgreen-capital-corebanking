<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audittrail extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','branch_id','user','module','notes',
    ];

    public function branches(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    
    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
}
