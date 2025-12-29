<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherIncomeType extends Model
{
    use HasFactory;
  protected $fillable=['name'];
  
    public function otherincom(){
        return $this->hasMany(OtherIncome::class);
    }
}
