import { Link } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
    FolderGit2,
    LayoutGrid,
    MessageSquare,
    ShoppingCart,
} from 'lucide-react';
import { index as adminEventsIndex } from '@/actions/App/Http/Controllers/Admin/EventController';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAuthorization } from '@/hooks/use-authorization';
import { dashboard } from '@/routes';
import { edit as adminGreetEdit } from '@/routes/admin/greet';
import { index as adminOrdersIndex } from '@/routes/admin/orders';
import type { NavItem } from '@/types';

/**
 * The `permission` values must match App\Enums\PermissionName exactly. Each one
 * also guards the matching route server-side in routes/web.php — the permission
 * here only decides whether the link is *rendered*, never whether the page is
 * reachable.
 *
 * Dashboard has no `permission` because every signed-in user may open it.
 */
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Greet Setting',
        href: adminGreetEdit(), // → /admin/greet
        icon: MessageSquare,
        permission: 'greet.view',
    },
    {
        title: 'Orders',
        href: adminOrdersIndex(), // → /admin/orders
        icon: ShoppingCart,
        permission: 'orders.view',
    },
    {
        title: 'Events',
        // Imported from @/actions rather than @/routes so this resolves without
        // waiting on a Wayfinder rebuild — the controller action file already
        // exists on disk, whereas @/routes/admin/events is only generated once
        // Vite next runs against the new named routes.
        href: adminEventsIndex(), // → /admin/events
        icon: CalendarDays,
        permission: 'events.view',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { can } = useAuthorization();

    // An item with no `permission` is always shown; one with a permission is shown
    // only if the user holds it. A super admin passes every check via the bypass
    // inside useAuthorization(), so they see the full menu.
    const visibleNavItems = mainNavItems.filter(
        (item) => !item.permission || can(item.permission),
    );

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={visibleNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
