<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import BaseSidebar from '@/components/navigation/BaseSidebar.vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ClipboardList,
    LayoutGrid,
    LogOut,
    Package,
    ScanLine,
    Settings,
    Truck,
} from 'lucide-vue-next';

defineProps<{
    supplierName?: string;
    shopName?: string | null;
}>();

const items = [
    { title: 'Dashboard', href: '/supplier-portal', icon: LayoutGrid },
    {
        title: 'Purchase orders',
        href: '/supplier-portal/purchase-orders',
        icon: ClipboardList,
    },
    { title: 'Supplies', href: '/supplier-portal/supplies', icon: Package },
    {
        title: 'Price list',
        href: '/supplier-portal/price-list',
        icon: ScanLine,
    },
];

// Account pages (profile, password, appearance) shared with the other roles.
const accountItems = [{ title: 'Settings', href: '/settings', icon: Settings }];

const page = usePage();
// Settings pages do not carry the supplier record, so fall back to the login's name.
const authName = (page.props.auth as { user?: { name?: string } } | undefined)
    ?.user?.name;

function isActive(href: string): boolean {
    const path = page.url.split('?')[0];
    return href === '/supplier-portal' ? path === href : path.startsWith(href);
}

function signOut() {
    router.post(logout.url());
}
</script>

<template>
    <BaseSidebar>
        <template #header>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/supplier-portal">
                            <AppLogoIcon class="size-8 shrink-0" />
                            <div
                                class="grid min-w-0 flex-1 text-left leading-tight"
                            >
                                <span
                                    class="flex items-center gap-1.5 truncate text-sm font-semibold"
                                >
                                    <Truck
                                        class="size-4 shrink-0 text-primary"
                                        aria-hidden="true"
                                    />
                                    {{ supplierName ?? authName ?? 'Supplier' }}
                                </span>
                                <span
                                    v-if="shopName"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    Supplier to {{ shopName }}
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>

        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel>Supplier portal</SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.href">
                    <SidebarMenuButton
                        as-child
                        :is-active="isActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>

        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel>Account</SidebarGroupLabel>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in accountItems" :key="item.href">
                    <SidebarMenuButton
                        as-child
                        :is-active="isActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>

        <template #footer>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton tooltip="Log out" @click="signOut">
                        <LogOut />
                        <span>Log out</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>
    </BaseSidebar>
</template>
