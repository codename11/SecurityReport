<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Main_Heading extends Model
{
    protected $fillable = [
        "obj_name", "sec_comp_name", "set_date", "user_id"
    ];

    protected $table = 'main_heading';

    public function user(){
        return $this->belongsTo("App\User", "user_id", "id");
    }

}
