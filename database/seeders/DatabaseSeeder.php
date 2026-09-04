<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles and permissions must exist before any user can be assigned one —
        // assignRole() looks the role up by name and silently attaches nothing if
        // the row is missing. Order matters here.
        $this->call(RolesAndPermissionsSeeder::class);

        // Three accounts, one per privilege level. Logging in as each is the
        // quickest way to see RBAC working: the sidebar visibly shrinks as you go
        // down the list. All of them use the password `password`, set by UserFactory.
        User::factory()
            ->create(['name' => 'Super Admin', 'email' => 'admin@example.com'])
            ->assignRole(RoleName::SuperAdmin);

        User::factory()
            ->create(['name' => 'Editor', 'email' => 'editor@example.com'])
            ->assignRole(RoleName::Editor);

        User::factory()
            ->create(['name' => 'Test User', 'email' => 'test@example.com'])
            ->assignRole(RoleName::User);
    }
}
