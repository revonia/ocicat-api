<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AttendancePin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_pin', function (Blueprint $table) {
            $table->integer('classn_id')->unsigned();
            $table->integer('lesson_id')->unsigned();
            $table->integer('pin')->unsigned();
            $table->timestamps();
        });

        $first = mt_rand(1001, 9999);
        DB::unprepared("ALTER TABLE attendance_pin AUTO_INCREMENT=$first;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_pin');
    }
}
