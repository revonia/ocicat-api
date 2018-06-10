<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class Attendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->integer('student_id')->unsigned()->index()->nullable();
            $table->integer('lesson_id')->unsigned()->index()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $first = rand(1001, 9999);
        DB::unprepared("ALTER TABLE attendances AUTO_INCREMENT=$first;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
