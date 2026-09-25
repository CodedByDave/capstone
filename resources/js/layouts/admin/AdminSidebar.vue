<script setup lang="ts">
import BaseSidebar from '@/components/navigation/BaseSidebar.vue'
import NavMain from '@/components/NavMain.vue'
import NavFooter from '@/components/NavFooter.vue'
import NavUser from '@/components/NavUser.vue'

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'

import { dashboard } from '@/routes'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import {
<<<<<<< HEAD
    LayoutGrid, Store, User, ListOrdered,
    ChartNoAxesCombined, Users, ShieldCheck,
} from 'lucide-vue-next'
import AppLogo from '@/components/AppLogo.vue'
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url(),
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
                title: 'Login Logs',
                href: '/admin/login-logs',
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
        href: '#',
        icon: ChartNoAxesCombined,
    },
<<<<<<< HEAD
]
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
</script>

<template>
    <BaseSidebar>
        <!-- HEADER -->
        <template #header>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
