<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum PermissionName: string
{
    case ViewEvents = 'events.view';
    case CreateEvents = 'events.create';
    case UpdateEvents = 'events.update';
    case DeleteEvents = 'events.delete';

    case ViewOrders = 'orders.view';
    case CreateOrders = 'orders.create';
    case UpdateOrders = 'orders.update';
    case DeleteOrders = 'orders.delete';

    case ViewGreetSetting = 'greet.view';
    case UpdateGreetSetting = 'greet.update';

    case ViewUsers = 'users.view';
    case ManageUsers = 'users.manage';

    public function label(): string
    {
        return match ($this) {
            self::ViewEvents => 'View events',
            self::CreateEvents => 'Create events',
            self::UpdateEvents => 'Update events',
            self::DeleteEvents => 'Delete events',
            self::ViewOrders => 'View orders',
            self::CreateOrders => 'Create orders',
            self::UpdateOrders => 'Update orders',
            self::DeleteOrders => 'Delete orders',
            self::ViewGreetSetting => 'View greet setting',
            self::UpdateGreetSetting => 'Update greet setting',
            self::ViewUsers => 'View users',
            self::ManageUsers => 'Manage users',
        };
    }

    /**
     * The UI grouping, derived from the part before the dot.
     */
    public function group(): string
    {
        return Str::before($this->value, '.');
    }

    /**
     * Build a `can:` middleware string, e.g. `can:events.create`.
     */
    public function middleware(): string
    {
        return 'can:'.$this->value;
    }
}
