<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { Head, router, usePage } from '@inertiajs/vue3';
import { LogOut, Truck } from 'lucide-vue-next';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

/** The supplier's own area: a slim top bar over a light grey page, with flash messages as toasts. */
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

function signOut() {
    router.post(logout.url());
}
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-muted/40">
        <header class="border-b bg-background">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <AppLogoIcon class="h-8 w-8 shrink-0" />
                    <div class="min-w-0">
                        <p
                            class="flex items-center gap-1.5 truncate text-sm font-semibold"
                        >
                            <Truck
                                class="h-4 w-4 shrink-0 text-primary"
                                aria-hidden="true"
                            />
                            {{ supplierName ?? 'Supplier' }}
                        </p>
                        <p
                            v-if="shopName"
                            class="truncate text-xs text-muted-foreground"
                        >
                            Supplier to {{ shopName }}
                        </p>
                    </div>
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    class="min-h-11 sm:min-h-9"
                    @click="signOut"
                >
                    <LogOut class="mr-1.5 h-4 w-4" aria-hidden="true" />
                    Log out
                </Button>
            </div>
        </header>
        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">
            <slot />
        </main>
    </div>
</template>
