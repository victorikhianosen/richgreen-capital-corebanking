<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollateralType extends Model
{
    use HasFactory;
    protected $fillable=[
        'name'
    ];

    public function collaterals(){
        return $this->hasMany(Collateral::class);
    }
}
