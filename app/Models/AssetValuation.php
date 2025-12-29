<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetValuation extends Model
{
    use HasFactory;
 protected $fillable =[
    'user_id','asset_id','branch_id','amount','date'
];

    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
