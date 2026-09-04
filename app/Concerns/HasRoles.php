<?php

namespace App\Concerns;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    /** @var Collection<int, string>|null */
    protected ?Collection $cachedRoleNames = null;

    /** @var Collection<int, string>|null */
    protected ?Collection $cachedPermissionNames = null;

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The names of every role assigned to this user.
     *
     * @return Collection<int, string>
     */
    public function roleNames(): Collection
    {
        return $this->cachedRoleNames ??= $this->roles()->pluck('name');
    }

    /**
     * The names of every permission granted through this user's roles.
     *
     * @return Collection<int, string>
     */
    public function permissionNames(): Collection
    {
        return $this->cachedPermissionNames ??= $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn (Role $role): Collection => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    public function hasRole(RoleName|string ...$roles): bool
    {
        return $this->roleNames()
            ->intersect(static::normalizeNames($roles))
            ->isNotEmpty();
    }

    public function hasPermissionTo(PermissionName|string $permission): bool
    {
        return $this->permissionNames()->contains(
            $permission instanceof PermissionName ? $permission->value : $permission,
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleName::SuperAdmin);
    }

    /**
     * Add roles without removing existing ones.
     */
    public function assignRole(RoleName|string ...$roles): static
    {
        $this->roles()->syncWithoutDetaching(
            Role::query()
                ->whereIn('name', static::normalizeNames($roles))
                ->pluck('id'),
        );

        return $this->flushRoleCache();
    }

    /**
     * Replace this user's roles with exactly the given ones.
     */
    public function syncRoles(RoleName|string ...$roles): static
    {
        $this->roles()->sync(
            Role::query()
                ->whereIn('name', static::normalizeNames($roles))
                ->pluck('id'),
        );

        return $this->flushRoleCache();
    }

    public function removeRole(RoleName|string ...$roles): static
    {
        $this->roles()->detach(
            Role::query()
                ->whereIn('name', static::normalizeNames($roles))
                ->pluck('id'),
        );

        return $this->flushRoleCache();
    }

    public function flushRoleCache(): static
    {
        $this->cachedRoleNames = null;
        $this->cachedPermissionNames = null;

        return $this;
    }

    /**
     * Normalise a mix of enum cases and raw slugs into a plain list of strings.
     *
     * The parameter is keyed `array-key`, not `int`, because PHP permits a
     * string-keyed array to be spread into a variadic — `...['a' => $role]` — so
     * the collected `...$roles` at each call site is not guaranteed to be a clean
     * 0..n list. array_values() then discards whatever keys came in and hands
     * callers the predictable list they expect (array_map on its own preserves
     * keys, which is what made the input type leak through to the return type).
     *
     * @param  array<array-key, RoleName|string>  $names
     * @return array<int, string>
     */
    protected static function normalizeNames(array $names): array
    {
        return array_values(array_map(
            fn (RoleName|string $name): string => $name instanceof RoleName ? $name->value : $name,
            $names,
        ));
    }
}
