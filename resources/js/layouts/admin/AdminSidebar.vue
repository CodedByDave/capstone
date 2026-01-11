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
import { LayoutGrid, Store, User, ListOrdered, ChartNoAxesCombined } from 'lucide-vue-next'
import AppLogo from '@/components/AppLogo.vue'

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '#',
        icon: User,
    },
    {
        title: 'Shop Management',
        href: '#',
        icon: Store,
    },
    {
        title: 'Order Management',
        href: '#',
        icon: ListOrdered,
    },
    {
        title: 'Analytics Report',
        href: '#',
        icon: ChartNoAxesCombined,
    },
]

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
