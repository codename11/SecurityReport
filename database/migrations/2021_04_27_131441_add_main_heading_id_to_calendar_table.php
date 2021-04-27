<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMainHeadingIdToCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('mh_id')->nullable();
            $table->foreign("mh_id")->references("id")->on("main_heading")->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('mh_id')->nullable();
            $table->foreign("mh_id")->references("id")->on("main_heading")->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
