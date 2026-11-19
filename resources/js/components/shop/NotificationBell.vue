<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCircle2, FileSignature, Inbox } from 'lucide-vue-next';
import {
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger,
} from 'reka-ui';
import { computed, ref, watch } from 'vue';

interface BellNotification {
    id: string;
    data: { type?: string; title?: string; body?: string; url?: string };
    read_at: string | null;
    created_at: string;
}

const page = usePage();
const { isOwner } = usePermissions();

const base = computed(() => (isOwner.value ? '/shop' : '/staff'));
const open = ref(false);
const loading = ref(false);
const failed = ref(false);
const onlyUnread = ref(false);
const items = ref<BellNotification[]>([]);

const unread = computed(() =>
    Number((page.props as any).unreadNotifications ?? 0),
);

const visible = computed(() =>
    onlyUnread.value ? items.value.filter((n) => !n.read_at) : items.value,
);

const xsrf = () =>
    decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

async function load() {
    loading.value = true;
    failed.value = false;
    try {
        const res = await fetch(`${base.value}/notifications/latest`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
        if (!res.ok) throw new Error('bad response');
        items.value = (await res.json()).notifications as BellNotification[];
    } catch {
        failed.value = true;
    } finally {
        loading.value = false;
    }
}

// Opening the panel only shows the latest items; nothing is marked read until one is clicked.
watch(open, (isOpen) => {
    if (isOpen) load();
});

async function openNotification(notification: BellNotification) {
    open.value = false;

    if (!notification.read_at) {
        try {
            await fetch(`${base.value}/notifications/${notification.id}/read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrf(),
                    Accept: 'application/json',
                },
            });
        } catch {
            // the link still opens; the dot clears on the next refresh
        }
        router.reload({ only: ['unreadNotifications'] });
    }

    if (notification.data.url) router.visit(notification.data.url);
}
</script>

<template>
    <PopoverRoot v-model:open="open">
        <PopoverTrigger as-child>
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="open ? 'bg-muted text-foreground' : ''"
                :aria-label="
                    unread > 0
                        ? `Notifications, ${unread} unread`
                        : 'Notifications'
                "
            >
                <Bell class="h-5 w-5" />
                <span
                    v-if="unread > 0"
                    class="absolute -top-1 -right-1 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white"
                >
                    {{ Math.min(unread, 99) }}
                </span>
            </button>
        </PopoverTrigger>

        <PopoverPortal>
            <PopoverContent
                align="end"
                :side-offset="8"
                class="z-50 w-[min(26rem,calc(100vw-1rem))] rounded-lg border bg-popover text-popover-foreground shadow-xl outline-none"
            >
                <div class="flex items-center justify-between gap-3 px-5 pt-4 pb-3">
                    <h2 class="text-lg font-semibold">Notifications</h2>
                    <label
                        class="flex cursor-pointer items-center gap-2 text-xs text-muted-foreground"
                    >
                        Only show unread
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="onlyUnread"
                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full border transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            :class="
                                onlyUnread
                                    ? 'border-blue-600 bg-blue-600'
                                    : 'border-input bg-muted'
                            "
                            @click="onlyUnread = !onlyUnread"
                        >
                            <span
                                class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform"
                                :class="
                                    onlyUnread
                                        ? 'translate-x-[18px]'
                                        : 'translate-x-[3px]'
                                "
                            />
                        </button>
                    </label>
                </div>

                <p
                    class="border-t px-5 pt-3 pb-1 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    Latest
                </p>

                <div class="max-h-[22rem] overflow-y-auto px-2 pb-2">
                    <p
                        v-if="loading && items.length === 0"
                        class="px-3 py-10 text-center text-sm text-muted-foreground"
                    >
                        Loading…
                    </p>
                    <p
                        v-else-if="failed"
                        class="px-3 py-10 text-center text-sm text-red-600"
                        role="alert"
                    >
                        Notifications could not be loaded.
                        <button type="button" class="underline" @click="load">
                            Try again
                        </button>
                    </p>
                    <div
                        v-else-if="visible.length === 0"
                        class="px-3 py-10 text-center"
                    >
                        <Inbox class="mx-auto h-9 w-9 text-stone-300" />
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{
                                onlyUnread
                                    ? 'No unread notifications.'
                                    : 'No notifications yet.'
                            }}
                        </p>
                    </div>
                    <ul v-else class="space-y-0.5">
                        <li v-for="n in visible" :key="n.id">
                            <button
                                type="button"
                                class="flex w-full items-start gap-3 rounded-md px-3 py-3 text-left hover:bg-muted/60 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                @click="openNotification(n)"
                            >
                                <span
                                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"
                                >
                                    <FileSignature
                                        v-if="
                                            n.data.type ===
                                            'business_agreement_executed'
                                        "
                                        class="h-4 w-4"
                                    />
                                    <CheckCircle2
                                        v-else-if="
                                            n.data.type === 'order_approved'
                                        "
                                        class="h-4 w-4"
                                    />
                                    <Bell v-else class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-sm font-semibold text-foreground"
                                    >
                                        {{ n.data.title || 'Platform update' }}
                                    </span>
                                    <span
                                        class="mt-0.5 line-clamp-2 block text-sm text-muted-foreground"
                                    >
                                        {{ n.data.body }}
                                    </span>
                                    <span
                                        class="mt-1 block text-xs text-muted-foreground"
                                    >
                                        {{ n.created_at }}
                                    </span>
                                </span>
                                <span
                                    v-if="!n.read_at"
                                    class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600"
                                    aria-label="Unread"
                                />
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="border-t p-2">
                    <Link
                        :href="`${base}/notifications`"
                        class="block rounded-md px-3 py-2.5 text-center text-sm font-medium text-blue-700 hover:bg-muted/60 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        @click="open = false"
                    >
                        View all notifications
                    </Link>
                </div>
            </PopoverContent>
        </PopoverPortal>
    </PopoverRoot>
</template>
