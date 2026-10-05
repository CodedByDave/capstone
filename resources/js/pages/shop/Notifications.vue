<script setup lang="ts">
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    CheckCircle2,
    Clock3,
    FileSignature,
    Inbox,
} from 'lucide-vue-next';

interface ShopNotification {
    id: string;
    data: {
        type?: string;
        title?: string;
        body?: string;
        url?: string;
    };
    read_at: string | null;
    created_at: string;
}

defineProps<{ notifications: ShopNotification[] }>();

function openNotification(notification: ShopNotification) {
    if (notification.data.url) {
        router.visit(notification.data.url);
    }
}
</script>

<template>
    <Head title="Notifications" />

    <ShopLayout title="Notifications">
        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6">
            <header
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Notifications
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Agreement, subscription, and platform updates for your
                        business.
                    </p>
                </div>
                <Button
                    v-if="
                        notifications.some(
                            (notification) => !notification.read_at,
                        )
                    "
                    variant="outline"
                    size="sm"
                    @click="router.post('/shop/notifications/read-all')"
                >
                    <CheckCircle2 class="mr-2 h-4 w-4" /> Mark all read
                </Button>
            </header>

            <div
                v-if="notifications.length === 0"
                class="border-y border-stone-300 py-16 text-center"
            >
                <Inbox class="mx-auto h-10 w-10 text-stone-300" />
                <p class="mt-3 font-medium text-stone-600">
                    No notifications yet
                </p>
            </div>

            <div v-else class="divide-y border-y border-stone-300">
                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    type="button"
                    class="flex w-full items-start gap-4 px-4 py-5 text-left transition hover:bg-muted/40"
                    :class="
                        !notification.read_at
                            ? 'bg-blue-50/60'
                            : 'bg-background'
                    "
                    @click="openNotification(notification)"
                >
                    <span
                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"
                    >
                        <FileSignature
                            v-if="
                                notification.data.type ===
                                'business_agreement_executed'
                            "
                            class="h-5 w-5"
                        />
                        <Bell v-else class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-3">
                            <span class="font-semibold text-foreground">
                                {{
                                    notification.data.title || 'Platform update'
                                }}
                            </span>
                            <span
                                v-if="!notification.read_at"
                                class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-600"
                            />
                        </span>
                        <span
                            class="mt-1 block text-sm leading-6 text-muted-foreground"
                        >
                            {{ notification.data.body }}
                        </span>
                        <span
                            class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <Clock3 class="h-3.5 w-3.5" />
                            {{ notification.created_at }}
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </ShopLayout>
</template>
