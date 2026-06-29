<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ────────────────────────────────────────────────────
        $admin          = Role::firstOrCreate(['name' => 'Admin']);
        $salesManager   = Role::firstOrCreate(['name' => 'Sales Manager']);
        $salesExecutive = Role::firstOrCreate(['name' => 'Sales Executive']);

        // ── Permissions ──────────────────────────────────────────────
        $permissions = [
            'view leads', 'create leads', 'edit leads', 'delete leads', 'assign leads',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            'view activities',
            'view dashboard',
            'manage users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Assign Permissions to Roles ───────────────────────────────
        $admin->syncPermissions(Permission::all());

        $salesManager->syncPermissions([
            'view leads', 'create leads', 'edit leads', 'assign leads',
            'view customers', 'create customers', 'edit customers',
            'view tasks', 'create tasks', 'edit tasks',
            'view activities', 'view dashboard',
        ]);

        $salesExecutive->syncPermissions([
            'view leads', 'create leads', 'edit leads',
            'view customers', 'edit customers',
            'view tasks', 'create tasks', 'edit tasks',
            'view activities', 'view dashboard',
        ]);

        // ── Seed Users ────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name'     => 'CRM Admin',
                'password' => Hash::make('Admin@1234'),
            ]
        );
        $adminUser->assignRole('Admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.com'],
            [
                'name'     => 'Sales Manager',
                'password' => Hash::make('Manager@1234'),
            ]
        );
        $manager->assignRole('Sales Manager');

        $exec = User::firstOrCreate(
            ['email' => 'exec@crm.com'],
            [
                'name'     => 'Sales Executive',
                'password' => Hash::make('Exec@1234'),
            ]
        );
        $exec->assignRole('Sales Executive');

        $this->command->info('✅ Roles, permissions and seed users created.');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@crm.com',   'Admin@1234',   'Admin'],
                ['manager@crm.com', 'Manager@1234', 'Sales Manager'],
                ['exec@crm.com',    'Exec@1234',    'Sales Executive'],
            ]
        );
    }
}
