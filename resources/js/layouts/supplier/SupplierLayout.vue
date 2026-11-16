<script setup lang="ts">
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { Head, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import AdminNavbar from '../admin/AdminNavbar.vue';
import SupplierSidebar from './SupplierSidebar.vue';

/** The supplier's own area: a sidebar of sections, a slim top bar and flash messages as toasts. */
defineProps<{
    title: string;
    supplierName?: string;
    shopName?: string | null;
}>();

interface ToastMessage {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
}

const page = usePage();

watch(
    () => page.props.toast as ToastMessage | undefined,
    (notification) => {
        if (!notification?.message) return;
        (toast[notification.type] ?? toast)(notification.message);
    },
    { immediate: true },
);
</script>

<template>
    <Head :title="title" />
    <SidebarProvider :default-open="true" storage-key="supplier-sidebar">
        <SupplierSidebar :supplier-name="supplierName" :shop-name="shopName" />

        <SidebarInset>
            <div class="flex min-h-screen flex-col bg-muted/40">
                <AdminNavbar :title="title" />
                <main
                    class="mx-auto w-full max-w-6xl flex-1 space-y-6 p-4 sm:p-6"
                >
                    <slot />
                </main>
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
