<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate a whole area of the app behind one or more roles.
 *
 * Registered as the `role` alias in `bootstrap/app.php`, so routes read:
 *
 *     Route::middleware(['auth', 'role:admin,super-admin'])->group(...)
 *
 * The variadic `...$roles` is what makes the comma-separated argument list work:
 * Laravel splits `role:admin,super-admin` on the comma and spreads the parts into
 * this method's parameters. The check is an OR — any one matching role passes.
 *
 * Use this for coarse "is this person staff?" checks on a route group. For the
 * individual actions inside that group, use the built-in `can:` middleware against
 * a permission instead (see `routes/web.php`). Gating single actions by role is the
 * classic RBAC mistake: the day you need an editor who may also delete events, you
 * end up editing route files instead of flipping one pivot row.
 */
class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Always chain this middleware *after* `auth`. When it is, `auth` has
        // already redirected guests to the login page and this line is merely
        // defensive — it stops the middleware from silently passing a null user
        // to hasRole() if someone forgets the `auth` in front of it.
        abort_if(! $user instanceof User, 401);

        // 403 rather than a redirect: the visitor is authenticated, they simply
        // are not allowed here. Redirecting would hide the failure and send an
        // admin in circles. Inertia turns this into an error page automatically.
        abort_if(! $user->hasRole(...$roles), 403, 'You do not have the required role to access this page.');

        return $next($request);
    }
}
