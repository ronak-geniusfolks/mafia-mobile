<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddInvoiceDeletePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the permission
        $permission = Permission::firstOrCreate(
            ['name' => 'invoices.delete'],
            ['guard_name' => 'web']
        );

        // Assign permission to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
            $this->command->info('Permission "invoices.delete" assigned to super-admin role.');
        } else {
            $this->command->warn('super-admin role not found. Permission created but not assigned to any role.');
        }

        $this->command->info('Permission "invoices.delete" has been created successfully.');
    }
}
