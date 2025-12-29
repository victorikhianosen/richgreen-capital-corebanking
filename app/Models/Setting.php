<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable=[
        'setting_key','setting_value'
    ];

    public function getsettingskey($keyname){
        $setts = Setting::where('setting_key', $keyname)->first();
        return !is_null($setts->setting_value) ? $setts->setting_value : "";
    }
}
