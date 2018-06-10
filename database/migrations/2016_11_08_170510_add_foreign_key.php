<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', function($table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('students', function($table){
            $table->foreign('classn_id')->references('id')->on('classns');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('teachers', function($table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('attendances', function($table){
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('lesson_id')->references('id')->on('lessons');
        });

        Schema::table('courses', function($table){
            $table->foreign('teacher_id')->references('id')->on('teachers');
        });

        Schema::table('lessons', function($table){
            $table->foreign('course_id')->references('id')->on('courses');
        });

        Schema::table('course_classn', function($table){
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade'); //TODO: 检查这里是否会产生错误
            $table->foreign('classn_id')->references('id')->on('classns')->onDelete('cascade');
        });

        Schema::table('user_profiles', function($table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('admins', function($table){
            $table->dropForeign(['user_id']);
        });

        Schema::table('students', function($table){
            $table->dropForeign(['classn_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('teachers', function($table){
            $table->dropForeign(['user_id']);
        });

        Schema::table('attendances', function($table){
            $table->dropForeign(['student_id']);
            $table->dropForeign(['lesson_id']);
        });

        Schema::table('courses', function($table){
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('lessons', function($table){
            $table->dropForeign(['course_id']);
        });

        Schema::table('course_classn', function($table){
            $table->dropForeign(['course_id']);
            $table->dropForeign(['classn_id']);
        });

        Schema::table('user_profiles', function($table){
            $table->dropForeign(['user_id']);
        });
    }
}
