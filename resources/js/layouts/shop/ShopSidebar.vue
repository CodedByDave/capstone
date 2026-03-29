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
    TrendingUp, Tag, Users, Briefcase, CalendarClock,
    Banknote, CreditCard, Receipt, Wallet,
    BadgeDollarSign, LineChart, BookOpen,
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
   HRM SUB-ACTIONS
───────────────────────────────────────── */
const allHrmSubActions = [
    {
        title: 'Employee List',
        icon: List,
        href: isOwner.value ? '/shop/employee' : '/staff/employee',
        show: () => isOwner.value || can('HRM', 'view'),
    },
    {
        title: 'Branch List',
        icon: Building2,
        href: isOwner.value ? '/shop/branch' : '/staff/branch',
        show: () => isOwner.value || can('HRM', 'view'),
    },
    {
        title: 'Attendance',
        icon: CalendarClock,
        href: isOwner.value ? '/shop/attendance' : '/staff/attendance',
        show: () => isOwner.value || can('HRM', 'view'),
    },
    {
        title: 'Roles & Positions',
        icon: Briefcase,
        href: isOwner.value ? '/shop/positions' : '/staff/positions',
        show: () => isOwner.value || can('HRM', 'view'),
    },
    {
        title: 'Activity Logs',
        icon: Clock,
        href: isOwner.value ? '/shop/logs' : '/staff/logs',
        show: () => isOwner.value || can('Activity Logs', 'view'),
    },
]

/* ─────────────────────────────────────────
   INVENTORY SUB-ACTIONS
───────────────────────────────────────── */
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
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
]

/* ─────────────────────────────────────────
   OPERATIONS SUB-ACTIONS
───────────────────────────────────────── */
const allOperationsSubActions = [
    {
        title: 'All Orders',
        icon: ShoppingCart,
        href: isOwner.value ? '/shop/orders' : '/staff/orders',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Pending',
        icon: ClipboardList,
        href: isOwner.value ? '/shop/orders/pending' : '/staff/orders/pending',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'In Progress',
        icon: Truck,
        href: isOwner.value ? '/shop/orders/progress' : '/staff/orders/progress',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Completed',
        icon: CheckCircle2,
        href: isOwner.value ? '/shop/orders/completed' : '/staff/orders/completed',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Cancelled',
        icon: XCircle,
        href: isOwner.value ? '/shop/orders/cancelled' : '/staff/orders/cancelled',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Service List',
        icon: Scissors,
        href: isOwner.value ? '/shop/services' : '/staff/services',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Pricing',
        icon: DollarSign,
        href: isOwner.value ? '/shop/services/pricing' : '/staff/services/pricing',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'Promotions',
        icon: Tag,
        href: isOwner.value ? '/shop/services/promos' : '/staff/services/promos',
        show: () => isOwner.value || can('Operations', 'view'),
    },
]

/* ─────────────────────────────────────────
   FINANCE MANAGEMENT SUB-ACTIONS
───────────────────────────────────────── */
const allFinanceSubActions = [
    {
        title: 'Overview',
        icon: Wallet,
        href: isOwner.value ? '/shop/finance' : '/staff/finance',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
    {
        title: 'Income',
        icon: BadgeDollarSign,
        href: isOwner.value ? '/shop/finance/income' : '/staff/finance/income',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
    {
        title: 'Expenses',
        icon: Receipt,
        href: isOwner.value ? '/shop/finance/expenses' : '/staff/finance/expenses',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
    {
        title: 'Payroll',
        icon: Banknote,
        href: isOwner.value ? '/shop/finance/payroll' : '/staff/finance/payroll',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
    {
        title: 'Transactions',
        icon: CreditCard,
        href: isOwner.value ? '/shop/finance/transactions' : '/staff/finance/transactions',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
]

/* ─────────────────────────────────────────
   REPORTS & ANALYTICS SUB-ACTIONS
───────────────────────────────────────── */
const allReportsSubActions = [
    {
        title: 'Overview',
        icon: PieChart,
        href: isOwner.value ? '/shop/reports' : '/staff/reports',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
        title: 'Sales Report',
        icon: TrendingUp,
        href: isOwner.value ? '/shop/reports/sales' : '/staff/reports/sales',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
        title: 'Inventory Report',
        icon: FileText,
        href: isOwner.value ? '/shop/reports/inventory' : '/staff/reports/inventory',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
        title: 'Finance Report',
        icon: LineChart,
        href: isOwner.value ? '/shop/reports/finance' : '/staff/reports/finance',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
        title: 'Audit Log',
        icon: BookOpen,
        href: isOwner.value ? '/shop/reports/audit' : '/staff/reports/audit',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
]

/* ─────────────────────────────────────────
   FILTERED COMPUTED LISTS
───────────────────────────────────────── */
const hrmSubActions       = computed(() => allHrmSubActions.filter(i => i.show()))
const inventorySubActions = computed(() => allInventorySubActions.filter(i => i.show()))
const operationsSubActions= computed(() => allOperationsSubActions.filter(i => i.show()))
const financeSubActions   = computed(() => allFinanceSubActions.filter(i => i.show()))
const reportsSubActions   = computed(() => allReportsSubActions.filter(i => i.show()))

function getSubActions(moduleName: string) {
    const map: Record<string, any[]> = {
        'HRM':                  hrmSubActions.value,
        'Inventory Management': inventorySubActions.value,
        'Operations':           operationsSubActions.value,
        'Finance Management':   financeSubActions.value,
        'Reports & Analytics':  reportsSubActions.value,
    }
    return map[moduleName] ?? []
}

/* ─────────────────────────────────────────
   MODULE CONFIG
───────────────────────────────────────── */
const moduleIconMap: Record<string, { icon: any; ownerHref: string; staffHref: string }> = {
    'HRM':                  { icon: Users,         ownerHref: '/shop/employee',  staffHref: '/staff/employee'  },
    'Inventory Management': { icon: Package,       ownerHref: '/shop/inventory', staffHref: '/staff/inventory' },
    'Operations':           { icon: ClipboardList, ownerHref: '/shop/orders',    staffHref: '/staff/orders'    },
    'Finance Management':   { icon: Banknote,      ownerHref: '/shop/finance',   staffHref: '/staff/finance'   },
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
    'HRM': (url) =>
        url.startsWith('/shop/employee')   || url.startsWith('/staff/employee')   ||
        url.startsWith('/shop/branch')     || url.startsWith('/staff/branch')     ||
        url.startsWith('/shop/attendance') || url.startsWith('/staff/attendance') ||
        url.startsWith('/shop/positions')  || url.startsWith('/staff/positions')  ||
        url.startsWith('/shop/logs')       || url.startsWith('/staff/logs'),
    'Inventory Management': (url) =>
        url.startsWith('/shop/inventory')  || url.startsWith('/staff/inventory')  ||
        url.startsWith('/shop/supplier')   || url.startsWith('/staff/supplier'),
    'Operations': (url) =>
        url.startsWith('/shop/orders')     || url.startsWith('/staff/orders')     ||
        url.startsWith('/shop/services')   || url.startsWith('/staff/services'),
    'Finance Management': (url) =>
        url.startsWith('/shop/finance')    || url.startsWith('/staff/finance'),
    'Reports & Analytics': (url) =>
        url.startsWith('/shop/reports')    || url.startsWith('/staff/reports'),
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
    '/shop/attendance','/staff/attendance',
    '/shop/positions', '/staff/positions',
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
    '/shop/finance',             '/staff/finance',
    '/shop/finance/income',      '/staff/finance/income',
    '/shop/finance/expenses',    '/staff/finance/expenses',
    '/shop/finance/payroll',     '/staff/finance/payroll',
    '/shop/finance/transactions','/staff/finance/transactions',
    '/shop/reports',           '/staff/reports',
    '/shop/reports/sales',     '/staff/reports/sales',
    '/shop/reports/inventory', '/staff/reports/inventory',
    '/shop/reports/finance',   '/staff/reports/finance',
    '/shop/reports/audit',     '/staff/reports/audit',
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
