<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainHeadingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main_heading', function (Blueprint $table) {
            $table->id();
            $table->string('obj_name');
            $table->string('sec_comp_name');
            $table->date('set_date');

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references("id")->on("users")->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('main_heading');
    }
}
