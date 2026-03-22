<script setup lang="ts">
import BaseSidebar from '@/components/navigation/BaseSidebar.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
    SidebarMenu, SidebarMenuButton, SidebarMenuItem,
    SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem,
} from '@/components/ui/sidebar'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Link, usePage, router } from '@inertiajs/vue3'
import {
    LayoutGrid, UserCircle, Package, ClipboardList,
    Tags, BarChart3, ShieldCheck, ChevronRight,
    Clock, Building2, List, Layers, AlertTriangle,
    ShoppingCart, CheckCircle2, XCircle, Truck,
    DollarSign, Scissors, PieChart, FileText,
    TrendingUp, Tag
} from 'lucide-vue-next'
import AppLogo from '@/components/AppLogo.vue'
import { computed, ref, watch } from 'vue'
import { usePermissions } from '@/composables/usePermissions'

const page = usePage()
const { props } = page
const { can, canAccessModule, isOwner, isStaff } = usePermissions()

const currentUrl = computed(() => page.url)
const isPaid = computed(() => props.order?.status === 'paid')

/* ─────────────────────────────────────────
   SUB-ACTIONS PER MODULE
───────────────────────────────────────── */

/* Employee Management */
const allEmployeeSubActions = [
    {
        title: 'Employee List',
        icon: List,
        href: isOwner.value ? '/shop/employee' : '/staff/employee',
        show: () => isOwner.value || can('Employee Management', 'view'),
    },
    {
        title: 'Branch List',
        icon: Building2,
        href: isOwner.value ? '/shop/branch' : '/staff/branch',
        show: () => isOwner.value || can('Employee Management', 'view'),
    },
    {
        title: 'Activity Logs',
        icon: Clock,
        href: isOwner.value ? '/shop/logs' : '/staff/logs',
        show: () => isOwner.value || can('Activity Logs', 'view'),
    },
]

/* Inventory Management */
const allInventorySubActions = [
    {
        title: 'Product List',
        icon: Package,
        href: isOwner.value ? '/shop/inventory' : '/staff/inventory',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
    {
        title: 'Categories',
        icon: Layers,
        href: isOwner.value ? '/shop/inventory/category' : '/staff/inventory/category',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
    {
        title: 'Stock Alerts',
        icon: AlertTriangle,
        href: isOwner.value ? '/shop/inventory/alerts' : '/staff/inventory/alerts',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
    {
        title: 'Suppliers',
        icon: Truck,
        href: isOwner.value ? '/shop/supplier' : '/staff/supplier',
        show: () => isOwner.value || can('Inventory Management', 'view')
    },
]

/* Order Management */
const allOrderSubActions = [
    {
        title: 'All Orders',
        icon: ShoppingCart,
        href: isOwner.value ? '/shop/orders' : '/staff/orders',
        show: () => isOwner.value || can('Order Management', 'view'),
    },
    {
        title: 'Pending',
        icon: ClipboardList,
        href: isOwner.value ? '/shop/orders/pending' : '/staff/orders/pending',
        show: () => isOwner.value || can('Order Management', 'view'),
    },
    {
        title: 'In Progress',
        icon: Truck,
        href: isOwner.value ? '/shop/orders/progress' : '/staff/orders/progress',
        show: () => isOwner.value || can('Order Management', 'view'),
    },
    {
        title: 'Completed',
        icon: CheckCircle2,
        href: isOwner.value ? '/shop/orders/completed' : '/staff/orders/completed',
        show: () => isOwner.value || can('Order Management', 'view'),
    },
    {
        title: 'Cancelled',
        icon: XCircle,
        href: isOwner.value ? '/shop/orders/cancelled' : '/staff/orders/cancelled',
        show: () => isOwner.value || can('Order Management', 'view'),
    },
]

/* Services & Pricing */
const allServicesSubActions = [
    {
        title: 'Service List',
        icon: Scissors,
        href: isOwner.value ? '/shop/services' : '/staff/services',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
    },
    {
        title: 'Pricing',
        icon: DollarSign,
        href: isOwner.value ? '/shop/services/pricing' : '/staff/services/pricing',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
    },
    {
        title: 'Promotions',
        icon: Tag,
        href: isOwner.value ? '/shop/services/promos' : '/staff/services/promos',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
    },
]

/* Filtered computed lists */
const employeeSubActions  = computed(() => allEmployeeSubActions.filter(i => i.show()))
const inventorySubActions = computed(() => allInventorySubActions.filter(i => i.show()))
const orderSubActions     = computed(() => allOrderSubActions.filter(i => i.show()))
const servicesSubActions  = computed(() => allServicesSubActions.filter(i => i.show()))


function getSubActions(moduleName: string) {
    const map: Record<string, any[]> = {
        'Employee Management':  employeeSubActions.value,
        'Inventory Management': inventorySubActions.value,
        'Order Management':     orderSubActions.value,
        'Services & Pricing':   servicesSubActions.value,
    }
    return map[moduleName] ?? []
}

/* ─────────────────────────────────────────
   MODULE CONFIG
───────────────────────────────────────── */

const moduleIconMap: Record<string, { icon: any; ownerHref: string; staffHref: string }> = {
    'Employee Management':  { icon: UserCircle,    ownerHref: '/shop/employee',  staffHref: '/staff/employee'  },
    'Inventory Management': { icon: Package,       ownerHref: '/shop/inventory', staffHref: '/staff/inventory' },
    'Order Management':     { icon: ClipboardList, ownerHref: '/shop/orders',    staffHref: '/staff/orders'    },
    'Services & Pricing':   { icon: Tags,          ownerHref: '/shop/services',  staffHref: '/staff/services'  },
    'Reports & Analytics':  { icon: BarChart3,     ownerHref: '/shop/reports',   staffHref: '/staff/reports'   },
}

function getHref(name: string): string {
    const map = moduleIconMap[name]
    if (!map) return '#'
    return isOwner.value ? map.ownerHref : map.staffHref
}

/* ─────────────────────────────────────────
   AREA DETECTION
───────────────────────────────────────── */

const areaChecks: Record<string, (url: string) => boolean> = {
    'Employee Management': (url) =>
        url.startsWith('/shop/employee')  || url.startsWith('/staff/employee')  ||
        url.startsWith('/shop/branch')    || url.startsWith('/staff/branch')    ||
        url.startsWith('/shop/logs')      || url.startsWith('/staff/logs'),
    'Inventory Management': (url) =>
        url.startsWith('/shop/inventory') || url.startsWith('/staff/inventory') ||
        url.startsWith('/shop/supplier')  || url.startsWith('/staff/supplier'),
    'Order Management': (url) =>
        url.startsWith('/shop/orders')    || url.startsWith('/staff/orders'),
    'Services & Pricing': (url) =>
        url.startsWith('/shop/services')  || url.startsWith('/staff/services'),
    'Reports & Analytics': (url) =>
        url.startsWith('/shop/reports')   || url.startsWith('/staff/reports'),
}

/* ─────────────────────────────────────────
   OPEN STATE
───────────────────────────────────────── */

const openModules = ref<Record<string, boolean>>(
    Object.fromEntries(
        Object.keys(areaChecks).map(name => [name, areaChecks[name](page.url)])
    )
)

watch(currentUrl, (url) => {
    Object.keys(areaChecks).forEach(name => {
        if (areaChecks[name](url)) openModules.value[name] = true
    })
})

function toggleCollapsible(name: string) {
    openModules.value[name] = !openModules.value[name]
}

/* ─────────────────────────────────────────
   SUB-ITEM ACTIVE STATE
───────────────────────────────────────── */

const exactRoutes = [
    '/shop/employee',  '/staff/employee',
    '/shop/branch',    '/staff/branch',
    '/shop/logs',      '/staff/logs',
    '/shop/inventory', '/staff/inventory',
    '/shop/inventory/category', '/staff/inventory/category',
    '/shop/inventory/alerts',   '/staff/inventory/alerts',
    '/shop/supplier',  '/staff/supplier',
    '/shop/orders',          '/staff/orders',
    '/shop/orders/pending',  '/staff/orders/pending',
    '/shop/orders/progress', '/staff/orders/progress',
    '/shop/orders/completed','/staff/orders/completed',
    '/shop/orders/cancelled','/staff/orders/cancelled',
    '/shop/services',          '/staff/services',
    '/shop/services/pricing',  '/staff/services/pricing',
    '/shop/services/promos',   '/staff/services/promos',
    '/shop/reports',           '/staff/reports',
    '/shop/reports/sales',     '/staff/reports/sales',
    '/shop/reports/inventory', '/staff/reports/inventory',
    '/shop/reports/payroll',   '/staff/reports/payroll',
]

function isSubActive(href: string): boolean {
    if (exactRoutes.includes(href)) {
        return currentUrl.value === href || currentUrl.value === `${href}/`
    }
    return currentUrl.value.startsWith(href)
}

/* ─────────────────────────────────────────
   NAV ITEMS
───────────────────────────────────────── */

interface ModuleNavItem {
    title: string
    href: string
    icon: any
    active: boolean
    hasSubMenu: boolean
}

const moduleNavItems = computed<ModuleNavItem[]>(() => {
    if (!isPaid.value || !props.order?.modules) return []

    return props.order.modules
        .filter((m: any) => isOwner.value || canAccessModule(m.name))
        .map((m: any): ModuleNavItem => ({
            title:      m.name,
            href:       getHref(m.name),
            icon:       moduleIconMap[m.name]?.icon ?? Package,
            active:     areaChecks[m.name]?.(currentUrl.value) ?? false,
            hasSubMenu: getSubActions(m.name).length > 0,
        }))
})

const dashboardItem = computed(() => [{
    title:  'Dashboard',
    href:   isOwner.value ? '/shop/dashboard' : '/staff/dashboard',
    icon:   LayoutGrid,
    active: currentUrl.value.includes('dashboard'),
}])
</script>

<template>
    <BaseSidebar>
        <template #header>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isOwner ? '/shop/dashboard' : '/staff/dashboard'">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>

        <NavMain :items="dashboardItem" />

        <SidebarMenu class="px-2 mt-1">
            <template v-for="mod in moduleNavItems" :key="mod.title">

                <!-- Collapsible module -->
                <template v-if="mod.hasSubMenu">
                    <Collapsible :open="openModules[mod.title] ?? false">
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                class="flex items-center justify-between w-full cursor-pointer"
                                :class="mod.active ? 'bg-muted/60 text-foreground' : 'hover:bg-muted/40'"
                                @click="toggleCollapsible(mod.title)"
                            >
                                <span class="flex items-center gap-2">
                                    <component :is="mod.icon" class="w-4 h-4 shrink-0" />
                                    <span>{{ mod.title }}</span>
                                </span>
                                <CollapsibleTrigger as-child>
                                    <span @click.stop="toggleCollapsible(mod.title)">
                                        <ChevronRight
                                            class="w-4 h-4 transition-transform duration-200"
                                            :class="(openModules[mod.title] ?? false) ? 'rotate-90' : ''"
                                        />
                                    </span>
                                </CollapsibleTrigger>
                            </SidebarMenuButton>

                            <CollapsibleContent>
                                <SidebarMenuSub class="ml-4 mt-0.5 border-l border-muted/50">
                                    <SidebarMenuSubItem
                                        v-for="sub in getSubActions(mod.title)"
                                        :key="sub.title"
                                    >
                                        <SidebarMenuSubButton
                                            class="flex items-center gap-2 text-xs cursor-pointer rounded-md px-2 py-1.5 w-full transition-colors"
                                            :class="isSubActive(sub.href)
                                                ? 'bg-muted/70 text-foreground font-medium'
                                                : 'text-muted-foreground hover:bg-muted/50'"
                                            @click="router.visit(sub.href)"
                                        >
                                            <component :is="sub.icon" class="w-3.5 h-3.5" />
                                            {{ sub.title }}
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </template>

                <!-- Regular item (no sub-menu) -->
                <template v-else>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            class="flex items-center gap-2 w-full cursor-pointer transition-colors"
                            :class="mod.active ? 'bg-muted/60 text-foreground' : 'hover:bg-muted/40'"
                            @click="router.visit(mod.href)"
                        >
                            <component :is="mod.icon" class="w-4 h-4 shrink-0" />
                            <span>{{ mod.title }}</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </template>

            </template>
        </SidebarMenu>

        <!-- Owner-only admin section -->
        <div v-if="isOwner && isPaid" class="px-2 mt-2">
            <p class="text-xs font-semibold text-muted-foreground px-2 mb-1 uppercase tracking-wide">
                Administration
            </p>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        class="flex items-center gap-2 w-full cursor-pointer transition-colors"
                        :class="currentUrl.startsWith('/shop/permission')
                            ? 'bg-muted/60 text-foreground'
                            : 'hover:bg-muted/40'"
                        @click="router.visit('/shop/permission')"
                    >
                        <ShieldCheck class="w-4 h-4" />
                        <span>Roles & Permission</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </div>

        <template #footer>
            <NavUser />
        </template>
    </BaseSidebar>
</template>
