<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import BaseSidebar from '@/components/navigation/BaseSidebar.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
<<<<<<< HEAD
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
=======
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { Link, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUp,
    BadgeDollarSign,
    Banknote,
    BarChart3,
    Building2,
    CalendarClock,
    ChevronRight,
    ClipboardList,
    Clock,
    CreditCard,
    FileText,
    Layers,
    LayoutGrid,
    List,
    Package,
    PieChart,
    QrCode,
    Receipt,
    Scissors,
    Settings,
    ShieldCheck,
    ShoppingCart,
    Tag,
    Truck,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

const page = usePage();
const { props } = page;
const { can, canAccessModule, isOwner } = usePermissions();

<<<<<<< HEAD
const currentUrl = computed(() => page.url)
const isPaid = computed(() => props.order?.status === 'paid')
=======
const currentUrl = computed(() => page.url);
const isPaid = computed(() =>
    ['paid', 'approved'].includes(props.order?.status ?? ''),
);
const platformPermissions = computed<string[]>(
    () => ((props as any).platformPermissions as string[] | undefined) ?? [],
);
const ownerCan = (permission: string) =>
    !isOwner.value || platformPermissions.value.includes(permission);

const shopStatus = computed(() => (props as any).shop?.status);

const isApproved = computed(
    () =>
        ['approved', 'paid'].includes(props.order?.status ?? '') &&
        (shopStatus.value ?? 'active') !== 'disabled',
);
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

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
        href: '/shop/logs',
        show: () => isOwner.value && ownerCan('shop.analytics'),
    },
];

/* Inventory Management */
const allInventorySubActions = [
    {
<<<<<<< HEAD
=======
        title: 'Categories',
        icon: Layers,
        href: isOwner.value
            ? '/shop/inventory/category'
            : '/staff/inventory/category',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
    {
        title: 'Suppliers',
        icon: Truck,
        href: isOwner.value ? '/shop/supplier' : '/staff/supplier',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
    {
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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
        href: isOwner.value
            ? '/shop/inventory/alerts'
            : '/staff/inventory/alerts',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
<<<<<<< HEAD
    {
        title: 'Suppliers',
        icon: Truck,
        href: isOwner.value ? '/shop/supplier' : '/staff/supplier',
        show: () => isOwner.value || can('Inventory Management', 'view'),
    },
]
=======
];
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

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
<<<<<<< HEAD
        href: isOwner.value ? '/shop/services' : '/staff/services',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
    },
    {
        title: 'Pricing',
        icon: DollarSign,
        href: isOwner.value ? '/shop/services/pricing' : '/staff/services/pricing',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
=======
        href: isOwner.value
            ? '/shop/operations/services'
            : '/staff/operations/services',
        show: () => isOwner.value || can('Operations', 'view'),
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
    },
    {
        title: 'Promotions',
        icon: Tag,
<<<<<<< HEAD
        href: isOwner.value ? '/shop/services/promos' : '/staff/services/promos',
        show: () => isOwner.value || can('Services & Pricing', 'view'),
=======
        href: isOwner.value
            ? '/shop/operations/promos'
            : '/staff/operations/promos',
        show: () => isOwner.value || can('Operations', 'view'),
    },
    {
        title: 'All Orders',
        icon: ShoppingCart,
        href: isOwner.value
            ? '/shop/operations/orders'
            : '/staff/operations/orders',
        show: () => isOwner.value || can('Operations', 'view'),
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
    },
];

<<<<<<< HEAD
/* Reports & Analytics */
=======
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
        title: 'Transactions',
        icon: CreditCard,
        href: isOwner.value
            ? '/shop/finance/transactions'
            : '/staff/finance/transactions',
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
        href: isOwner.value
            ? '/shop/finance/expenses'
            : '/staff/finance/expenses',
        show: () => isOwner.value || can('Finance Management', 'view'),
    },
];

/* ─────────────────────────────────────────
   REPORTS & ANALYTICS SUB-ACTIONS
───────────────────────────────────────── */
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
const allReportsSubActions = [
    {
        title: 'Overview',
        icon: PieChart,
        href: isOwner.value ? '/shop/reports' : '/staff/reports',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
<<<<<<< HEAD
        title: 'Sales Report',
        icon: TrendingUp,
        href: isOwner.value ? '/shop/reports/sales' : '/staff/reports/sales',
=======
        title: 'Employee Report',
        icon: Users,
        href: isOwner.value
            ? '/shop/reports/employee'
            : '/staff/reports/employee',
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
    {
        title: 'Inventory Report',
        icon: FileText,
        href: isOwner.value
            ? '/shop/reports/inventory'
            : '/staff/reports/inventory',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
<<<<<<< HEAD
    {
        title: 'Payroll Report',
        icon: DollarSign,
        href: isOwner.value ? '/shop/reports/payroll' : '/staff/reports/payroll',
        show: () => isOwner.value || can('Reports & Analytics', 'view'),
    },
]

/* Filtered computed lists */
const employeeSubActions = computed(() => allEmployeeSubActions.filter(i => i.show()))
const inventorySubActions = computed(() => allInventorySubActions.filter(i => i.show()))
const orderSubActions = computed(() => allOrderSubActions.filter(i => i.show()))
const servicesSubActions = computed(() => allServicesSubActions.filter(i => i.show()))
const reportsSubActions = computed(() => allReportsSubActions.filter(i => i.show()))

function getSubActions(moduleName: string) {
    const map: Record<string, any[]> = {
        'Employee Management': employeeSubActions.value,
        'Inventory Management': inventorySubActions.value,
        'Order Management': orderSubActions.value,
        'Services & Pricing': servicesSubActions.value,
        'Reports & Analytics': reportsSubActions.value,
    }
    return map[moduleName] ?? []
=======
];

/* ─────────────────────────────────────────
   FILTERED COMPUTED LISTS
───────────────────────────────────────── */
const hrmSubActions = computed(() => allHrmSubActions.filter((i) => i.show()));
const inventorySubActions = computed(() =>
    allInventorySubActions.filter((i) => i.show()),
);
const operationsSubActions = computed(() =>
    allOperationsSubActions.filter((i) => i.show()),
);
const financeSubActions = computed(() =>
    allFinanceSubActions.filter((i) => i.show()),
);
const reportsSubActions = computed(() =>
    allReportsSubActions.filter((i) => i.show()),
);

function getSubActions(moduleName: string) {
    const map: Record<string, any[]> = {
        HRM: hrmSubActions.value,
        'Inventory Management': inventorySubActions.value,
        Operations: operationsSubActions.value,
        'Finance Management': financeSubActions.value,
        'Reports & Analytics': reportsSubActions.value,
    };
    return map[moduleName] ?? [];
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
}

/* ─────────────────────────────────────────
   MODULE CONFIG
───────────────────────────────────────── */
<<<<<<< HEAD

const moduleIconMap: Record<string, { icon: any; ownerHref: string; staffHref: string }> = {
    'Employee Management': { icon: UserCircle, ownerHref: '/shop/employee', staffHref: '/staff/employee' },
    'Inventory Management': { icon: Package, ownerHref: '/shop/inventory', staffHref: '/staff/inventory' },
    'Order Management': { icon: ClipboardList, ownerHref: '/shop/orders', staffHref: '/staff/orders' },
    'Services & Pricing': { icon: Tags, ownerHref: '/shop/services', staffHref: '/staff/services' },
    'Reports & Analytics': { icon: BarChart3, ownerHref: '/shop/reports', staffHref: '/staff/reports' },
}
=======
const moduleIconMap: Record<
    string,
    { icon: any; ownerHref: string; staffHref: string }
> = {
    HRM: {
        icon: Users,
        ownerHref: '/shop/employee',
        staffHref: '/staff/employee',
    },
    'Inventory Management': {
        icon: Package,
        ownerHref: '/shop/inventory',
        staffHref: '/staff/inventory',
    },
    Operations: {
        icon: ClipboardList,
        ownerHref: '/shop/operations/orders',
        staffHref: '/staff/operations/orders',
    },
    'Finance Management': {
        icon: Banknote,
        ownerHref: '/shop/finance',
        staffHref: '/staff/finance',
    },
    'Reports & Analytics': {
        icon: BarChart3,
        ownerHref: '/shop/reports',
        staffHref: '/staff/reports',
    },
};
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

function getHref(name: string): string {
    const map = moduleIconMap[name];
    if (!map) return '#';
    return isOwner.value ? map.ownerHref : map.staffHref;
}

/* ─────────────────────────────────────────
   AREA DETECTION
───────────────────────────────────────── */

const areaChecks: Record<string, (url: string) => boolean> = {
<<<<<<< HEAD
    'Employee Management': (url) =>
        url.startsWith('/shop/employee') || url.startsWith('/staff/employee') ||
        url.startsWith('/shop/branch') || url.startsWith('/staff/branch') ||
        url.startsWith('/shop/logs'),
    'Inventory Management': (url) =>
        url.startsWith('/shop/inventory') || url.startsWith('/staff/inventory')||
        url.startsWith('/shop/supplier') || url.startsWith('/staff/supplier'),
    'Order Management': (url) =>
        url.startsWith('/shop/orders') || url.startsWith('/staff/orders'),
    'Services & Pricing': (url) =>
        url.startsWith('/shop/services') || url.startsWith('/staff/services'),
    'Reports & Analytics': (url) =>
        url.startsWith('/shop/reports') || url.startsWith('/staff/reports'),
}
=======
    HRM: (url) =>
        url.startsWith('/shop/employee') ||
        url.startsWith('/staff/employee') ||
        url.startsWith('/shop/branch') ||
        url.startsWith('/staff/branch') ||
        url.startsWith('/shop/attendance') ||
        url.startsWith('/staff/attendance') ||
        url.startsWith('/shop/positions') ||
        url.startsWith('/staff/positions') ||
        url.startsWith('/shop/logs') ||
        url.startsWith('/staff/logs'),

    'Inventory Management': (url) =>
        url.startsWith('/shop/inventory') ||
        url.startsWith('/staff/inventory') ||
        url.startsWith('/shop/supplier') ||
        url.startsWith('/staff/supplier'),

    Operations: (url) =>
        url.startsWith('/shop/operations') ||
        url.startsWith('/staff/operations') ||
        url.startsWith('/shop/orders') ||
        url.startsWith('/staff/orders') ||
        url.startsWith('/shop/services') ||
        url.startsWith('/staff/services'),

    'Finance Management': (url) =>
        url.startsWith('/shop/finance') || url.startsWith('/staff/finance'),

    'Reports & Analytics': (url) =>
        url.startsWith('/shop/reports') || url.startsWith('/staff/reports'),
};
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

/* ─────────────────────────────────────────
   OPEN STATE
───────────────────────────────────────── */

const openModules = ref<Record<string, boolean>>(
    Object.fromEntries(
        Object.keys(areaChecks).map((name) => [
            name,
            areaChecks[name](page.url),
        ]),
    ),
);

watch(currentUrl, (url) => {
    Object.keys(areaChecks).forEach((name) => {
        if (areaChecks[name](url)) openModules.value[name] = true;
    });
});

function toggleCollapsible(name: string) {
    openModules.value[name] = !openModules.value[name];
}

/* ─────────────────────────────────────────
   SUB-ITEM ACTIVE STATE
───────────────────────────────────────── */

const exactRoutes = [
<<<<<<< HEAD
    '/shop/employee', '/shop/branch', '/staff/employee', '/staff/branch', '/shop/logs',
    '/shop/inventory', '/shop/inventory/categories', '/shop/inventory/alerts',
    '/staff/inventory', '/staff/inventory/categories', '/staff/inventory/alerts',
    '/shop/supplier', '/staff/supplier',
    '/shop/orders', '/shop/orders/pending', '/shop/orders/progress', '/shop/orders/completed', '/shop/orders/cancelled',
    '/staff/orders', '/staff/orders/pending', '/staff/orders/progress', '/staff/orders/completed', '/staff/orders/cancelled',
    '/shop/services', '/shop/services/pricing', '/shop/services/promos',
    '/staff/services', '/staff/services/pricing', '/staff/services/promos',
    '/shop/reports', '/shop/reports/sales', '/shop/reports/inventory', '/shop/reports/payroll',
    '/staff/reports', '/staff/reports/sales', '/staff/reports/inventory', '/staff/reports/payroll',
]
=======
    '/shop/employee',
    '/staff/employee',
    '/shop/branch',
    '/staff/branch',
    '/shop/attendance',
    '/staff/attendance',
    '/shop/positions',
    '/staff/positions',
    '/shop/logs',
    '/staff/logs',
    '/shop/inventory',
    '/staff/inventory',
    '/shop/inventory/category',
    '/staff/inventory/category',
    '/shop/inventory/alerts',
    '/staff/inventory/alerts',
    '/shop/supplier',
    '/staff/supplier',
    '/shop/operations/orders',
    '/staff/operations/orders',
    '/shop/operations/services',
    '/staff/operations/services',
    '/shop/operations/promos',
    '/staff/operations/promos',
    '/shop/orders',
    '/staff/orders',
    '/shop/orders/pending',
    '/staff/orders/pending',
    '/shop/orders/progress',
    '/staff/orders/progress',
    '/shop/orders/completed',
    '/staff/orders/completed',
    '/shop/orders/cancelled',
    '/staff/orders/cancelled',
    '/shop/services',
    '/staff/services',
    '/shop/services/pricing',
    '/staff/services/pricing',
    '/shop/services/promos',
    '/staff/services/promos',
    '/shop/finance',
    '/staff/finance',
    '/shop/finance/income',
    '/staff/finance/income',
    '/shop/finance/expenses',
    '/staff/finance/expenses',
    '/shop/finance/payroll',
    '/staff/finance/payroll',
    '/shop/finance/transactions',
    '/staff/finance/transactions',
    '/shop/reports',
    '/staff/reports',
    '/shop/reports/sales',
    '/staff/reports/sales',
    '/shop/reports/inventory',
    '/staff/reports/inventory',
    '/shop/reports/finance',
    '/staff/reports/finance',
    '/shop/reports/audit',
    '/staff/reports/audit',
];
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

function isSubActive(href: string): boolean {
    if (exactRoutes.includes(href)) {
        return currentUrl.value === href || currentUrl.value === `${href}/`;
    }
    return currentUrl.value.startsWith(href);
}

/* ─────────────────────────────────────────
   NAV ITEMS
───────────────────────────────────────── */

interface ModuleNavItem {
    title: string;
    href: string;
    icon: any;
    active: boolean;
    hasSubMenu: boolean;
}

const moduleNavItems = computed<ModuleNavItem[]>(() => {
<<<<<<< HEAD
    if (!isPaid.value || !props.order?.modules) return []

    return props.order.modules
        .filter((m: any) => isOwner.value || canAccessModule(m.name))
        .map((m: any): ModuleNavItem => ({
            title: m.name,
            href: getHref(m.name),
            icon: moduleIconMap[m.name]?.icon ?? Package,
            active: areaChecks[m.name]?.(currentUrl.value) ?? false,
            hasSubMenu: getSubActions(m.name).length > 0,
        }))
})

const dashboardItem = computed(() => [{
    title: 'Dashboard',
    href: isOwner.value ? '/shop/dashboard' : '/staff/dashboard',
    icon: LayoutGrid,
    active: currentUrl.value.includes('dashboard'),
}])
=======
    if (!isPaid.value || !props.order?.modules) return [];
    if (!isApproved.value || !props.order?.modules) return [];

    const ownerModulePermissions: Record<string, string> = {
        HRM: 'shop.staff',
        'Inventory Management': 'shop.inventory',
        Operations: 'shop.operations',
        'Finance Management': 'shop.finance',
        'Reports & Analytics': 'shop.analytics',
    };

    return props.order.modules
        .filter((m: any) =>
            isOwner.value
                ? ownerCan(ownerModulePermissions[m.name] ?? '')
                : canAccessModule(m.name),
        )
        .map(
            (m: any): ModuleNavItem => ({
                title: m.name,
                href: getHref(m.name),
                icon: moduleIconMap[m.name]?.icon ?? Package,
                active: areaChecks[m.name]?.(currentUrl.value) ?? false,
                hasSubMenu: getSubActions(m.name).length > 0,
            }),
        );
});

const dashboardItem = computed(() => {
    if (isOwner.value && !ownerCan('shop.dashboard')) return [];

    return [
        {
            title: 'Dashboard',
            href: isOwner.value ? '/shop/dashboard' : '/staff/dashboard',
            icon: LayoutGrid,
            active: currentUrl.value.includes('dashboard'),
        },
    ];
});

const ownerHomeHref = computed(() =>
    ownerCan('shop.dashboard') ? '/shop/dashboard' : '/',
);
const hasOwnerAdministration = computed(() =>
    ['shop.operations', 'shop.staff', 'shop.settings', 'shop.finance'].some(
        ownerCan,
    ),
);
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
</script>

<template>
    <BaseSidebar>
        <template #header>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link
                            :href="isOwner ? ownerHomeHref : '/staff/dashboard'"
                        >
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>

        <NavMain :items="dashboardItem" />

        <SidebarMenu class="mt-1 px-2">
            <template v-for="mod in moduleNavItems" :key="mod.title">
                <!-- Collapsible module -->
                <template v-if="mod.hasSubMenu">
                    <Collapsible :open="openModules[mod.title] ?? false">
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                class="flex w-full cursor-pointer items-center justify-between"
                                :class="
                                    mod.active
                                        ? 'bg-muted/60 text-foreground'
                                        : 'hover:bg-muted/40'
                                "
                                @click="toggleCollapsible(mod.title)"
                            >
                                <span class="flex items-center gap-2">
                                    <component
                                        :is="mod.icon"
                                        class="h-4 w-4 shrink-0"
                                    />
                                    <span>{{ mod.title }}</span>
                                </span>
                                <CollapsibleTrigger as-child>
                                    <span
                                        @click.stop="
                                            toggleCollapsible(mod.title)
                                        "
                                    >
                                        <ChevronRight
                                            class="h-4 w-4 transition-transform duration-200"
                                            :class="
                                                (openModules[mod.title] ??
                                                false)
                                                    ? 'rotate-90'
                                                    : ''
                                            "
                                        />
                                    </span>
                                </CollapsibleTrigger>
                            </SidebarMenuButton>

                            <CollapsibleContent>
<<<<<<< HEAD
                                <SidebarMenuSub class="ml-4 mt-0.5 border-l border-muted/50">
                                    <SidebarMenuSubItem v-for="sub in getSubActions(mod.title)" :key="sub.title">
                                        <SidebarMenuSubButton
                                            class="flex items-center gap-2 text-xs cursor-pointer rounded-md px-2 py-1.5 w-full transition-colors"
                                            :class="isSubActive(sub.href)
                                                ? 'bg-muted/70 text-foreground font-medium'
                                                : 'text-muted-foreground hover:bg-muted/50'"
                                            @click="router.visit(sub.href)">
                                            <component :is="sub.icon" class="w-3.5 h-3.5" />
                                            {{ sub.title }}
=======
                                <SidebarMenuSub
                                    class="mt-0.5 ml-4 border-l border-muted/50"
                                >
                                    <SidebarMenuSubItem
                                        v-for="sub in getSubActions(mod.title)"
                                        :key="sub.title"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="isSubActive(sub.href)"
                                        >
                                            <Link
                                                :href="sub.href"
                                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs transition-colors"
                                            >
                                                <component
                                                    :is="sub.icon"
                                                    class="h-3.5 w-3.5"
                                                />
                                                {{ sub.title }}
                                            </Link>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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
<<<<<<< HEAD
                        <SidebarMenuButton class="flex items-center gap-2 w-full cursor-pointer transition-colors"
                            :class="mod.active ? 'bg-muted/60 text-foreground' : 'hover:bg-muted/40'"
                            @click="router.visit(mod.href)">
                            <component :is="mod.icon" class="w-4 h-4 shrink-0" />
                            <span>{{ mod.title }}</span>
=======
                        <SidebarMenuButton as-child :is-active="mod.active">
                            <Link
                                :href="mod.href"
                                class="flex w-full items-center gap-2"
                            >
                                <component
                                    :is="mod.icon"
                                    class="h-4 w-4 shrink-0"
                                />
                                <span>{{ mod.title }}</span>
                            </Link>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </template>
            </template>
        </SidebarMenu>

        <!-- Owner-only admin section -->
<<<<<<< HEAD
        <div v-if="isOwner && isPaid" class="px-2 mt-2">
            <p class="text-xs font-semibold text-muted-foreground px-2 mb-1 uppercase tracking-wide">
                Administration
            </p>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton class="flex items-center gap-2 w-full cursor-pointer transition-colors" :class="currentUrl.startsWith('/shop/permission')
                        ? 'bg-muted/60 text-foreground'
                        : 'hover:bg-muted/40'" @click="router.visit('/shop/permission')">
                        <ShieldCheck class="w-4 h-4" />
                        <span>Roles & Permission</span>
=======
        <div
            v-if="isOwner && isApproved && hasOwnerAdministration"
            class="mt-2 px-2"
        >
            <p
                class="mb-1 px-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Administration
            </p>
            <SidebarMenu>
                <SidebarMenuItem v-if="ownerCan('shop.operations')">
                    <SidebarMenuButton
                        as-child
                        :is-active="currentUrl.startsWith('/shop/logistics')"
                    >
                        <Link
                            href="/shop/logistics"
                            class="flex w-full items-center gap-2"
                        >
                            <Truck class="h-4 w-4" />
                            <span>Logistics</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="ownerCan('shop.staff')">
                    <SidebarMenuButton
                        as-child
                        :is-active="currentUrl.startsWith('/shop/permission')"
                    >
                        <Link
                            href="/shop/permission"
                            class="flex w-full items-center gap-2"
                        >
                            <ShieldCheck class="h-4 w-4" />
                            <span>Roles & Permission</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="ownerCan('shop.settings')">
                    <SidebarMenuButton
                        as-child
                        :is-active="currentUrl.startsWith('/shop/upgrade')"
                    >
                        <Link
                            href="/shop/upgrade"
                            class="flex w-full items-center gap-2"
                        >
                            <ArrowUp class="h-4 w-4" />
                            <span>Upgrade Plan</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="ownerCan('shop.finance')">
                    <SidebarMenuButton
                        as-child
                        :is-active="currentUrl.startsWith('/shop/payment-qr')"
                    >
                        <Link
                            href="/shop/payment-qr"
                            class="flex w-full items-center gap-2"
                        >
                            <QrCode class="h-4 w-4" />
                            <span>Payment Settings</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                <SidebarMenuItem v-if="ownerCan('shop.settings')">
                    <SidebarMenuButton
                        as-child
                        :is-active="currentUrl.startsWith('/shop/settings')"
                    >
                        <Link
                            href="/shop/settings"
                            class="flex w-full items-center gap-2"
                        >
                            <Settings class="h-4 w-4" />
                            <span>Shop Settings</span>
                        </Link>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </div>

        <template #footer>
            <NavUser />
        </template>
    </BaseSidebar>
</template>
