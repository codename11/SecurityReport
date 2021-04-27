<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $table = 'calendar';
    protected $fillable = [
        "user_id"
    ];

    protected $casts = [
        "names" => "array",
        "daynums" => "array",
    ];

    public function user(){
        return $this->belongsTo("App\User", "user_id", "id");
    }

    public function main_heading(){
        return $this->belongsTo("App\Main_Heading", "mh_id", "id");
    }

}
