<script setup lang="ts">
import BaseSidebar from '@/components/navigation/BaseSidebar.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'

import { dashboard } from '@/routes'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutGrid,
    UserCircle,
    Package,
    ClipboardList,
    Tags,
    BarChart3,
    ShieldCheck,
} from 'lucide-vue-next'

import AppLogo from '@/components/AppLogo.vue'
import { computed } from 'vue'

const { props } = usePage<{
    auth: {
        user: {
            id: number
            role: 'owner' | 'staff'
            permitted_modules?: {
                name: string
                actions: string[]
            }[]
        }
    }
    order?: {
        status: string
        modules: { name: string; price: number }[]
    }
}>()

const isOwner = computed(() => props.auth.user.role === 'owner')
const isStaff = computed(() => props.auth.user.role === 'staff')
const isPaid  = computed(() => props.order?.status === 'paid')

const moduleIconMap: Record<string, { icon: any; href: string }> = {
    'Employee Management':  { icon: UserCircle,    href: '/shop/employee' },
    'Inventory Management': { icon: Package,       href: '/shop/inventory' },
    'Order Management':     { icon: ClipboardList, href: '/shop/orders' },
    'Services & Pricing':   { icon: Tags,          href: '/shop/services' },
    'Reports & Analytics':  { icon: BarChart3,     href: '/shop/reports' },
}

const moduleNavItems = computed(() => {
    if (!isPaid.value || !props.order?.modules) return []

    if (isOwner.value) {
        return props.order.modules.map((m) => ({
            title: m.name,
            href:  moduleIconMap[m.name]?.href ?? `/shop/modules/${m.name.toLowerCase().replace(/\s+/g, '-')}`,
            icon:  moduleIconMap[m.name]?.icon ?? Package,
        }))
    }

    if (isStaff.value) {
        const permitted = props.auth.user.permitted_modules?.map((p) => p.name) ?? []
        return props.order.modules
            .filter((m) => permitted.includes(m.name))
            .map((m) => ({
                title: m.name,
                href:  moduleIconMap[m.name]?.href ?? `/shop/modules/${m.name.toLowerCase().replace(/\s+/g, '-')}`,
                icon:  moduleIconMap[m.name]?.icon ?? Package,
            }))
    }

    return []
})

// Merge Dashboard + modules into one nav list
const allNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href:  dashboard(),
        icon:  LayoutGrid,
    },
    ...moduleNavItems.value,
])
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

        <!-- MAIN NAV: Dashboard + all module items in one list, no gap -->
        <NavMain :items="allNavItems" />

        <!-- OWNER-ONLY: Administration -->
        <div v-if="isOwner && isPaid" class="px-2 mt-2">
            <p class="text-xs font-semibold text-muted-foreground px-2 mb-1 uppercase tracking-wide">
                Administration
            </p>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child>
                        <Link href="/shop/permissions" class="flex items-center gap-2 w-full">
                            <ShieldCheck class="w-4 h-4 text-primary" />
                            <span>Module Permissions</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </div>

        <!-- FOOTER -->
        <template #footer>
            <NavUser />
        </template>
    </BaseSidebar>
</template>
