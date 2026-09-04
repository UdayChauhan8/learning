import { usePage } from '@inertiajs/react';

/**
 * Must match App\Enums\RoleName::SuperAdmin->value.
 *
 * Kept as a constant because the super-admin bypass has to be mirrored on the
 * client: on the server, `Gate::before` grants a super admin every ability
 * without consulting the permissions table, so their shared `permissions` array
 * would still be checked literally here and the sidebar would come up empty.
 */
const SUPER_ADMIN = 'super-admin';

export type UseAuthorizationReturn = {
    /** True when the user holds this exact permission (or is a super admin). */
    can: (permission: string) => boolean;
    /** True when the user holds at least one of the given permissions. */
    canAny: (...permissions: string[]) => boolean;
    /** True when the user holds at least one of the given roles. */
    hasRole: (...roles: string[]) => boolean;
    isSuperAdmin: boolean;
};

/**
 * Read the roles and permissions shared by HandleInertiaRequests::share().
 *
 * This is for PRESENTATION ONLY — deciding which links and buttons to render.
 * It is not a security boundary. Everything the server shares is visible in the
 * page payload and can be tampered with in the browser, which is why every admin
 * route in routes/web.php carries its own `can:` middleware. Treat this hook as a
 * way to avoid showing people buttons that would 403 them.
 *
 * @example
 * const { can } = useAuthorization();
 * {can('events.delete') && <Button variant="destructive">Delete</Button>}
 */
export function useAuthorization(): UseAuthorizationReturn {
    const { auth } = usePage().props;

    // Defensive `?? []`: unauthenticated pages (login, welcome) share an auth
    // object with no user, and older cached page props may predate these keys.
    const roles = auth?.roles ?? [];
    const permissions = auth?.permissions ?? [];

    const isSuperAdmin = roles.includes(SUPER_ADMIN);

    const can = (permission: string): boolean =>
        isSuperAdmin || permissions.includes(permission);

    return {
        can,
        canAny: (...list: string[]): boolean => list.some(can),
        hasRole: (...list: string[]): boolean =>
            isSuperAdmin || list.some((role) => roles.includes(role)),
        isSuperAdmin,
    };
}
