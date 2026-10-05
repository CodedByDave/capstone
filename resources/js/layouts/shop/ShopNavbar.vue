<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';

const page = usePage();

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
            <Link
                href="/shop/notifications"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
                aria-label="Shop notifications"
            >
                <Bell class="h-5 w-5" />
                <span
                    v-if="
                        Number((page.props as any).unreadNotifications ?? 0) > 0
                    "
                    class="absolute -top-1 -right-1 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white"
                >
                    {{
                        Math.min(
                            Number((page.props as any).unreadNotifications),
                            99,
                        )
                    }}
                </span>
            </Link>
        </div>
    </header>
</template>
