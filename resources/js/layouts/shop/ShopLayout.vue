<script setup lang="ts">
import {
    SidebarProvider,
    SidebarInset,
} from '@/components/ui/sidebar'
import AdminSidebar from './ShopSidebar.vue'
import AdminNavbar from './ShopNavbar.vue'
import ShopSidebar from './ShopSidebar.vue';
defineProps<{
    title: string
}>()
</script>

<template>
    <SidebarProvider :default-open="true" storage-key="shop-sidebar">
        <ShopSidebar />

        <SidebarInset>
            <div class="flex min-h-screen flex-col">
                <AdminNavbar :title="title" />
                <main class="flex-1 p-4">
                    <slot />
                </main>
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
