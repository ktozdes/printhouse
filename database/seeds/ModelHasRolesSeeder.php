<?php

use Illuminate\Database\Seeder;

class ModelHasRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_roles')->insert([
	    	[
	    		'model_type' => 'App\User', 
	    		'model_id' => '1', 
	    		'role_id' => '3', 
	    	],[
	    		'model_type' => 'App\User', 
	    		'model_id' => '2', 
	    		'role_id' => '1', 
	    	],
   	 	]);
    }
}
