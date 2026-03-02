<script setup lang="ts">
import { SidebarProvider, SidebarInset } from '@/components/ui/sidebar'
import ShopSidebar from './ShopSidebar.vue'
import ShopNavbar from './ShopNavbar.vue'
import { Toaster } from 'vue-sonner'
import { type BreadcrumbItem } from '@/types'

defineProps<{
    title: string
    breadcrumbs?: BreadcrumbItem[]
}>()
</script>

<template>
    <SidebarProvider :default-open="true" storage-key="shop-sidebar" :breadcrumbs="breadcrumbs">
        <ShopSidebar />

        <SidebarInset>
            <div class="flex min-h-screen flex-col">
                <ShopNavbar :title="title" />
                <main class="flex-1 p-4">
                    <slot />
                </main>
            </div>
        </SidebarInset>
    </SidebarProvider>

    <!-- Global toast notifications -->
    <Toaster position="top-right" rich-colors close-button />
</template>
