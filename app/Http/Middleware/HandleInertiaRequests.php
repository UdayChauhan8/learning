<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,

                // Flattened name arrays rather than the nested roles/permissions
                // relations. Two reasons: the payload stays small (a handful of
                // strings instead of full model graphs with timestamps), and the
                // React side can do a plain `includes()` check without walking a
                // nested structure.
                //
                // These drive the `useAuthorization()` hook, which decides which
                // sidebar links and buttons to render. That is presentation only —
                // hiding a button is NOT authorization. Every admin route enforces
                // its own `can:` middleware server-side, because anything shared
                // with the browser is visible and editable by the visitor.
                //
                // The `?? []` matters: a guest has no user, and the React hook
                // expects arrays, not null.
                'roles' => $user?->roleNames()->all() ?? [],
                'permissions' => $user?->permissionNames()->all() ?? [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
