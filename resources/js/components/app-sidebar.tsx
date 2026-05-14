import { usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { CalendarDays, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
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
import { index as eventsIndex } from '@/routes/events';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Events',
        href: eventsIndex(),
        icon: CalendarDays,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Users',
        href: usersIndex(),
        icon: Users,
    },
];

export function AppSidebar() {
    const { auth } = usePage().props;
    const isAdmin = auth.user?.role === 'admin';

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={eventsIndex()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent>
                <NavMain items={mainNavItems} />
                {isAdmin && <NavMain items={adminNavItems} />}
            </SidebarContent>
            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
