<script setup lang="ts">
import {
    SidebarProvider,
    SidebarInset,
} from '@/components/ui/sidebar'
import AdminSidebar from './AdminSidebar.vue'
import AdminNavbar from './AdminNavbar.vue'

defineProps<{
    title: string
}>()
</script>

<template>
    <SidebarProvider :default-open="true" storage-key="admin-sidebar">
        <!-- Persistent Admin Sidebar -->
        <AdminSidebar />

        <!-- Main content -->
        <SidebarInset>
            <div class="flex min-h-screen flex-col">
                <AdminNavbar :title="title" />
                <main class="flex-1 p-4">
                    <!-- Slot for page content -->
                    <slot />
                </main>
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
