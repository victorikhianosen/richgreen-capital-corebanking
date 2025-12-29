<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','asset_type_id','branch_id','purchase_date','purchase_price','replacement_value','initial','serial_number','bought_from','note','file'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assettype(){
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }
    
    public function branch(){
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function assetvalues(){
        return $this->hasMany(AssetValuation::class);
    }
}
