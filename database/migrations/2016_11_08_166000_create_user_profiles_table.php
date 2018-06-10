<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->primary('user_id');
            $table->string('nickname')->nullable();
            $table->string('realname')->nullable();
            $table->string('age')->nullable();
            $table->enum('gender', ['secret', 'male', 'female', 'others'])->default('secret');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        $first = rand(1001, 9999);
        DB::unprepared("ALTER TABLE user_profiles AUTO_INCREMENT=$first;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_profiles');
    }
}
