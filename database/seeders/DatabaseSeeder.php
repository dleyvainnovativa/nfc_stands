<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Platform owner (no tenant).
        User::updateOrCreate(
            ['email' => 'admin@nfc.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'tenant_id' => null,
                'is_active' => true,
            ]
        );

        // Demo tenant + its client-admin, so you can see tenant-scoped views.
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo-restaurant'],
            [
                'name' => 'Demo Restaurant',
                'contact_email' => 'owner@demo.test',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'client@nfc.com'],
            [
                'name' => 'Demo Client',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT_ADMIN,
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );
    }
}
