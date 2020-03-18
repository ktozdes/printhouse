<?php

use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('statuses')->truncate();
        DB::table('statuses')->insert([
        	['name' => 'Создан'],
        	['name' => 'В процессе'],
        	['name' => 'Завершен'],
        ]);
    }
}
