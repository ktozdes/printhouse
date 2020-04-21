<?php

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        //create roles
        $clientRole = Role::create(['name' => 'client']);
        $managerRole = Role::create(['name' => 'manager']);
        $superAdminRole = Role::create(['name' => 'super-admin']);

        // create menu permissions
        Permission::create(['name' => 'menu dashboard']);
        Permission::create(['name' => 'menu order']);
        Permission::create(['name' => 'menu defect']);
        Permission::create(['name' => 'menu report']);
        Permission::create(['name' => 'menu user']);
        Permission::create(['name' => 'menu storage']);
        Permission::create(['name' => 'menu payment']);

        // create page permissions
        Permission::create(['name' => 'order user all']);

        Permission::create(['name' => 'report chart']);
        Permission::create(['name' => 'report balance']);
        Permission::create(['name' => 'report order']);
        Permission::create(['name' => 'report storage']);
        Permission::create(['name' => 'report user all']);

        Permission::create(['name' => 'profile edit personal']);
        Permission::create(['name' => 'profile edit additional']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $clientRole->givePermissionTo([
        	'menu order', 'menu report',
        	'report balance', 'report order', 
        	'profile edit personal'
        ]);
        $managerRole->givePermissionTo([
        	'menu dashboard', 'menu order', 'menu report', 'menu payment', 'menu user', 'menu defect',
        	'order user all',
        	'report balance', 'report order', 'report storage', 'report user all',
        	'profile edit personal'
        ]);
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
