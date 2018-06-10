<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(AllDataSeeder::class);
        $this->call(CallTheRollDataSeeder::class);
        $this->call(DemoDataSeeder::class);
        Model::reguard();
    }
}
