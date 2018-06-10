<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->string('role_type')->nullable();
            $table->integer('role_id')->nullable()->unsigned();
            //$table->softDeletes();
            $table->timestamps();
        });

        $first = mt_rand(1001, 9999);
        DB::unprepared("ALTER TABLE users AUTO_INCREMENT=$first;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
