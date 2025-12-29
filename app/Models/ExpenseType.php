<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable=[
        'expcat','name'
    ];

    public function expense(){
        return $this->hasMany(Expenses::class);
    }
}
