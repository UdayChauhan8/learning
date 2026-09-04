<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Sync the roles and permissions defined in the enums into the database.
 *
 * The enums are the source of truth for *what exists*; these tables are only the
 * storage that lets a role be linked to a permission. That split is deliberate —
 * it means a typo'd permission is a PHP error at the call site rather than a row
 * that silently never matches.
 *
 * This seeder is safe to run repeatedly. Run it again after adding a new enum case
 * and it will insert the new permission and attach it to the roles that should
 * have it, without disturbing which roles your existing users hold.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: make sure a row exists for every permission the code knows about.
        //
        // updateOrCreate keyed on `name` is what makes this idempotent: the slug is
        // the identity, and label/group are refreshed on each run so editing an
        // enum label() propagates without a migration.
        foreach (PermissionName::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission->value],
                ['label' => $permission->label(), 'group' => $permission->group()],
            );
        }

        // Step 2: create each role and attach exactly the permissions it should have.
        foreach (RoleName::cases() as $roleName) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName->value],
                ['label' => $roleName->label()],
            );

            // sync() — not syncWithoutDetaching() — on purpose. sync() makes the
            // pivot mirror defaultPermissions() exactly, so *removing* a permission
            // from the enum actually revokes it on the next run. With
            // syncWithoutDetaching() permissions could only ever accumulate, and a
            // role would quietly keep access you thought you had taken away.
            //
            // This only touches permission_role. Nobody's role assignments in
            // role_user are affected, so re-seeding never logs an admin out of
            // their own admin panel.
            $role->permissions()->sync(
                Permission::query()
                    ->whereIn('name', array_map(
                        fn (PermissionName $permission): string => $permission->value,
                        $roleName->defaultPermissions(),
                    ))
                    ->pluck('id'),
            );
        }
    }
}
