<script setup lang="ts">
import NotificationBell from '@/components/shop/NotificationBell.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { useShopOrderPopups } from '@/composables/useShopOrderPopups';
import { type BreadcrumbItem } from '@/types';

const { isOwner } = usePermissions();

useShopOrderPopups(() =>
    isOwner.value ? '/shop/notifications/poll' : '/staff/notifications/poll',
);

defineProps<{
    title: string;
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <header
        class="flex h-16 items-center gap-3 border-b border-sidebar-border bg-background px-4"
    >
        <!-- Sidebar toggle -->
        <SidebarTrigger />

        <!-- Page title -->
        <h1 class="text-sm font-semibold text-foreground">
            {{ title }}
        </h1>
        <!-- Right side -->
        <div class="ml-auto flex items-center gap-2">
            <NotificationBell />
        </div>
    </header>
</template>
