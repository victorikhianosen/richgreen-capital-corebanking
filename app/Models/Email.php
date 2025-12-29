<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','branch_id','subject','message','recipient'
    ];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
     public function customer(){
        return $this->belongsTo(Customer::class,'user_id');
    }
}
