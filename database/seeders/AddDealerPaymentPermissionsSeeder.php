<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddDealerPaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define dealer payment permissions
        $permissions = [
            'dealer-payments.view',
            'dealer-payments.create',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );

            $this->command->info("Permission '{$permissionName}' created.");
        }

        // Assign all dealer payment permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && ! $superAdminRole->hasPermissionTo($permission)) {
                    $superAdminRole->givePermissionTo($permission);
                    $this->command->info("Permission '{$permissionName}' assigned to super-admin role.");
                }
            }
            $this->command->info('All dealer payment permissions have been assigned to super-admin role.');
        } else {
            $this->command->warn('super-admin role not found. Permissions created but not assigned to any role.');
        }

        $this->command->info('Dealer payment permissions have been created successfully.');
    }
}
