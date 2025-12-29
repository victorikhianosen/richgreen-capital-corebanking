<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $fillable=[
        'sector','branch_id'
    ];

    public function Loans(){
        return $this->hasMany(Loan::class);
    }
}
