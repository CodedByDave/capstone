<script setup lang="ts">
import BaseSidebar from '@/components/navigation/BaseSidebar.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import AppLogo from '@/components/AppLogo.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    ChartNoAxesCombined,
    CircleAlert,
    KeyRound,
    LayoutGrid,
    ListOrdered,
    ShieldCheck,
    Store,
    User,
    Users,
} from 'lucide-vue-next';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '#',
        icon: User,
        children: [
            {
                title: 'User List',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Audit & Logs',
                href: '/admin/audit-logs',
                icon: ShieldCheck,
            },
        ],
    },
    {
        title: 'Shop Management',
        href: '/admin/shop',
        icon: Store,
    },
    {
        title: 'Order Management',
        href: '/admin/orders',
        icon: ListOrdered,
    },
    {
        title: 'Analytics Report',
        href: '/admin/analytics',
        icon: ChartNoAxesCombined,
    },
    {
        title: 'Issue Reports',
        href: '/admin/issue-reports',
        icon: CircleAlert,
    },
    {
        title: 'Roles & Permissions',
        href: '/admin/settings/roles-permissions',
        icon: KeyRound,
    },
];
</script>

<template>
    <BaseSidebar>
        <!-- HEADER -->
        <template #header>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>

        <!-- MAIN -->
        <NavMain :items="mainNavItems" />

        <!-- FOOTER -->
        <template #footer>
            <NavUser />
        </template>
    </BaseSidebar>
</template>
