<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->integer('pin')->unsigned();
            $table->primary('pin');
        });
        //wake 11111
        //眼鼻嘴 222222
        //Roll in the deep 333333
        //Opera 2 424242
        //那一年  20161020
        //身骑白马 20170425
        //The Rap 252148
        //我的小森林 04040404
        //宇多田ヒカル - 花束を君に 20170506
        DB::table('pins')->insert(
            [
//                ['pin' => 11111],
                ['pin' => 222222],
                ['pin' => 333333],
                ['pin' => 424242],
                ['pin' => 20161020],
                ['pin' => 20170425],
                ['pin' => 252148],
                ['pin' => 04040404],
                ['pin' => 20170506],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pins');
    }
}
