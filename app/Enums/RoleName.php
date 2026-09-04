<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Admin => 'Administrator',
            self::Editor => 'Editor',
            self::User => 'User',
        };
    }

    /**
     * The permissions granted to this role when seeding.
     *
     * @return array<int, PermissionName>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SuperAdmin => PermissionName::cases(),
            self::Admin => [
                PermissionName::ViewEvents,
                PermissionName::CreateEvents,
                PermissionName::UpdateEvents,
                PermissionName::DeleteEvents,
                PermissionName::ViewOrders,
                PermissionName::CreateOrders,
                PermissionName::UpdateOrders,
                PermissionName::DeleteOrders,
                PermissionName::ViewGreetSetting,
                PermissionName::UpdateGreetSetting,
                PermissionName::ViewUsers,
            ],
            self::Editor => [
                PermissionName::ViewEvents,
                PermissionName::CreateEvents,
                PermissionName::UpdateEvents,
                PermissionName::ViewOrders,
                PermissionName::UpdateOrders,
                PermissionName::ViewGreetSetting,
                PermissionName::UpdateGreetSetting,
            ],
            self::User => [],
        };
    }

    /**
     * Build a `role:` middleware string, e.g. `role:admin,super-admin`.
     */
    public static function middleware(self ...$roles): string
    {
        return 'role:'.implode(',', array_map(
            fn (self $role): string => $role->value,
            $roles,
        ));
    }
}
