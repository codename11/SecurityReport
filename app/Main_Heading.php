<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Main_Heading extends Model
{
    protected $fillable = [
        "obj_name", "sec_comp_name", "set_date", "user_id"
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
