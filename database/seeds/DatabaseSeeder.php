<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(PlateSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(FileSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ModelHasRolesSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(StorageSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
