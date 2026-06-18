<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddAttachmentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define attachment permissions
        $attachmentPermissions = [
            'attachments.view',
            'attachments.export',
        ];

        // Create permissions
        foreach ($attachmentPermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );

            $this->command->info("Permission '{$permissionName}' created.");
        }

        // Assign all attachment permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            foreach ($attachmentPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && ! $superAdminRole->hasPermissionTo($permission)) {
                    $superAdminRole->givePermissionTo($permission);
                    $this->command->info("Permission '{$permissionName}' assigned to super-admin role.");
                }
            }
            $this->command->info('All attachment permissions have been assigned to super-admin role.');
        } else {
            $this->command->warn('super-admin role not found. Permissions created but not assigned to any role.');
        }

        $this->command->info('Attachment permissions have been created successfully.');
    }
}
