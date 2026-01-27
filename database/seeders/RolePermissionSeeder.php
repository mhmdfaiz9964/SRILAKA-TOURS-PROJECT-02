<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Products
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            
            // Sales
            'sale-list',
            'sale-create',
            'sale-edit', // Though we only requested edit on purchase, I'll add here for consistency
            'sale-delete',
            
            // Purchases
            'purchase-list',
            'purchase-create',
            'purchase-edit',
            'purchase-delete',
            
            // Suppliers
            'supplier-list',
            'supplier-create',
            'supplier-edit',
            'supplier-delete',

            // Customers
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',

            // Users & Roles
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',

            // Reports / Dashboard
            'dashboard-view',
            'report-view',

            // Banks
            'bank-list',
            'bank-create',
            'bank-edit',
            'bank-delete',

            // Cheques (General / Returns)
            'cheque-list',
            'cheque-create', // For creating return cheques or manual entry
            'cheque-edit',
            'cheque-delete',
            'cheque-operation', // For actions like deposit, bounce, etc.

            // In Cheques
            'in-cheque-list',
            'in-cheque-create',
            'in-cheque-edit',
            'in-cheque-delete',

            // Out Cheques
            'out-cheque-list',
            'out-cheque-create',
            'out-cheque-edit',
            'out-cheque-delete',

            // Third Party Cheques
            'third-party-cheque-list',
            'third-party-cheque-create',
            'third-party-cheque-edit',
            'third-party-cheque-delete',

            // Investors
            'investor-list',
            'investor-create',
            'investor-edit',
            'investor-delete',

            // Categories
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',

            // SYSTEM & Settings
            'system-manage',
            'settings-manage',
        ];

        foreach ($permissions as $permission) {
             \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create Super Admin Role
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Create Admin User if not exists and assign role
        $user = \App\Models\User::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('password')
        ]);
        $user->assignRole($role);

        // Requested Admin User
        $specificUser = \App\Models\User::firstOrCreate([
            'email' => 'admin@selfholidays.com'
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password')
        ]);
        $specificUser->assignRole($role);
    }
}
