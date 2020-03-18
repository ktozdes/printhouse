<?php

use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('storages')->truncate();
        factory('App\Storage', 100)->create();
    }
}
