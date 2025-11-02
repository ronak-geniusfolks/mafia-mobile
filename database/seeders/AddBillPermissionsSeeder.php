<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddBillPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define bill permissions
        $billPermissions = [
            'bills.view',
            'bills.create',
            'bills.edit',
            'bills.delete',
            'bills.detail',
            'bills.print',
        ];

        // Create permissions
        foreach ($billPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );

            $this->command->info("Permission '{$permissionName}' created.");
        }

        // Assign all bill permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            foreach ($billPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$superAdminRole->hasPermissionTo($permission)) {
                    $superAdminRole->givePermissionTo($permission);
                    $this->command->info("Permission '{$permissionName}' assigned to super-admin role.");
                }
            }
            $this->command->info('All bill permissions have been assigned to super-admin role.');
        } else {
            $this->command->warn('super-admin role not found. Permissions created but not assigned to any role.');
        }

        $this->command->info('Bill permissions have been created successfully.');
    }
}
