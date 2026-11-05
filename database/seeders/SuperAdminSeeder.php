<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'mafiamobile36@gmail.com'],
            [
                'name' => 'Mafia Mobile',
                'user_name' => 'mafia_mobile',
                'password' => Hash::make('Mafia.mobile.2025'), // Make sure to update in prod
            ]
        );

        // 2. Create 'super-admin' Role
        $role = Role::firstOrCreate(['name' => 'super-admin']);

        // 3. Define Permissions
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Profile
            'profile.view',
            'profile.update',
            'profile.password.change',

            // Purchases
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',
            'purchases.import',
            'purchases.download.stock',

            // Sales
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.fetch.stock',

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.print',
            'invoices.detail',
            'invoices.delete',

            // Reports
            'reports.sales.view',
            'reports.sales.export',
            'reports.purchases.view',
            'reports.purchases.export',
            'reports.customers.view',
            'reports.customers.export',
            'reports.charts.view',

            // Expenses
            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',

            // Transactions
            'transactions.view',
            'transactions.create',
            'transactions.edit',
            'transactions.delete',
            'transactions.resync',

            // Google Sync
            'google.sync',

            // Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
        ];

        // 4. Create Permissions & Assign to Role
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
            $role->givePermissionTo($permission);
        }

        // 5. Assign Role to Super Admin
        $admin->assignRole($role);
    }
}
