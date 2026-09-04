<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
    }

    /**
     * Route every authorization check through the user's role-based permissions.
     *
     * `Gate::before` runs ahead of every gate and policy in the application, which
     * is what lets us support arbitrary permission names (`events.create`) without
     * registering a `Gate::define()` for each one. Defining them individually would
     * mean querying the `permissions` table on every single request — including
     * during `php artisan migrate` on a fresh database, where the table does not
     * exist yet. This callback is lazy instead: it only touches the database when
     * an ability is actually checked.
     *
     * The return value is the subtle part, and getting it wrong breaks the app:
     *
     *   true  -> granted, immediately. Nothing else runs.
     *   null  -> "no opinion". Laravel continues to the normal gate/policy lookup.
     *   false -> DENIED outright, and every policy in the app is skipped.
     *
     * So the miss case below must return `null`, never `false`. Returning `false`
     * would make RBAC subtractive: any ability not backed by a permission row
     * would be hard-denied, and a Policy class you write later would never be
     * consulted. Returning `null` keeps RBAC additive — permissions grant access,
     * and anything they don't cover still falls through to your policies.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            // Super admins bypass the permission table entirely. This is why the
            // seeder can hand them every permission and it still doesn't matter:
            // they are authorized for abilities that don't even exist yet.
            if ($user->isSuperAdmin()) {
                return true;
            }

            // Permission names are dotted (`events.delete`) precisely so they can
            // never collide with a policy ability name like `update` or `viewAny`.
            // A policy check therefore misses here and falls through to the policy.
            return $user->hasPermissionTo($ability) ? true : null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
