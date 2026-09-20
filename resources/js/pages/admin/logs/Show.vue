<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeft,
    CalendarClock,
    Globe,
    Laptop,
    LogIn,
    Mail,
    Shield,
    Store,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface AuditLog {
    record_id: number;
    category: 'authentication' | 'activity';
    user_public_id: string | null;
    name: string | null;
    email: string | null;
    role: string | null;
    module: string;
    event: string;
    details: string | null;
    ip_address: string | null;
    user_agent: string | null;
    shop_name: string | null;
    occurred_at: string;
}

const { log } = defineProps<{ log: AuditLog }>();

const parsedDetails = computed<Record<string, unknown> | null>(() => {
    if (!log.details) return null;
    try {
        const value = JSON.parse(log.details);
        return value && typeof value === 'object' ? value : null;
    } catch {
        return null;
    }
});

const detailRows = computed(() => {
    if (!parsedDetails.value) return [];

    return Object.entries(parsedDetails.value).flatMap(([field, value]) => {
        if (field === 'target_user' && value && typeof value === 'object') {
            return Object.entries(value as Record<string, unknown>).map(
                ([targetField, targetValue]) => ({
                    field: `Target ${label(targetField)}`,
                    oldValue: null,
                    newValue: displayValue(targetValue),
                }),
            );
        }

        if (
            value &&
            typeof value === 'object' &&
            ('old' in value || 'new' in value)
        ) {
            const change = value as { old?: unknown; new?: unknown };
            return [
                {
                    field: label(field),
                    oldValue: displayValue(change.old),
                    newValue: displayValue(change.new),
                },
            ];
        }

        return [
            {
                field: label(field),
                oldValue: null,
                newValue: displayValue(value),
            },
        ];
    });
});

function label(value: string) {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function displayValue(value: unknown) {
    if (value === null || value === undefined || value === '') return '—';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
}

function formatDate(value: string) {
    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'long',
        timeStyle: 'medium',
    }).format(new Date(value));
}

function parseAgent(userAgent: string | null) {
    if (!userAgent) return 'Not recorded';
    if (/Edg/i.test(userAgent)) return 'Microsoft Edge';
    if (/Chrome/i.test(userAgent)) return 'Google Chrome';
    if (/Firefox/i.test(userAgent)) return 'Mozilla Firefox';
    if (/Safari/i.test(userAgent)) return 'Safari';
    return userAgent;
}
</script>

<template>
    <Head :title="`${label(log.event)} - Audit Event`" />

    <AdminLayout title="Audit Event Details">
        <div class="mx-auto max-w-5xl space-y-6 px-6">
            <Button
                variant="outline"
                @click="router.visit('/admin/audit-logs')"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Audit & Logs
            </Button>

            <Card>
                <CardContent class="p-6">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-xl"
                                :class="
                                    log.category === 'authentication'
                                        ? 'bg-indigo-100 text-indigo-700'
                                        : 'bg-emerald-100 text-emerald-700'
                                "
                            >
                                <LogIn
                                    v-if="log.category === 'authentication'"
                                    class="h-7 w-7"
                                />
                                <Activity v-else class="h-7 w-7" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    {{ log.module }}
                                </p>
                                <h1 class="text-2xl font-semibold">
                                    {{ label(log.event) }}
                                </h1>
                            </div>
                        </div>
                        <span
                            class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                log.category === 'authentication'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'bg-emerald-100 text-emerald-700'
                            "
                        >
                            {{
                                log.category === 'authentication'
                                    ? 'Authentication'
                                    : 'User Activity'
                            }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <UserRound class="h-5 w-5 text-primary" />
                            Actor
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5 text-sm">
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Name
                            </p>
                            <p class="mt-1 font-medium">
                                {{ log.name || 'Unknown user' }}
                            </p>
                        </div>
                        <div class="flex items-start gap-3">
                            <Mail
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Email
                                </p>
                                <p class="mt-1 font-medium break-all">
                                    {{ log.email || 'Not recorded' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Shield
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Role
                                </p>
                                <p class="mt-1 font-medium capitalize">
                                    {{
                                        log.role?.replace('_', ' ') ||
                                        'Not recorded'
                                    }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <CalendarClock class="h-5 w-5 text-primary" />
                            Event Context
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5 text-sm">
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Date and time
                            </p>
                            <p class="mt-1 font-medium">
                                {{ formatDate(log.occurred_at) }}
                            </p>
                        </div>
                        <div
                            v-if="log.shop_name"
                            class="flex items-start gap-3"
                        >
                            <Store
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Shop
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ log.shop_name }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="log.ip_address"
                            class="flex items-start gap-3"
                        >
                            <Globe
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    IP address
                                </p>
                                <p class="mt-1 font-mono font-medium">
                                    {{ log.ip_address }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="log.user_agent"
                            class="flex items-start gap-3"
                        >
                            <Laptop
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Browser / device
                                </p>
                                <p
                                    class="mt-1 font-medium"
                                    :title="log.user_agent"
                                >
                                    {{ parseAgent(log.user_agent) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Activity Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="detailRows.length"
                        class="overflow-hidden rounded-lg border"
                    >
                        <table class="w-full text-sm">
                            <thead
                                class="bg-muted/60 text-xs text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-3 text-left">Field</th>
                                    <th
                                        v-if="
                                            detailRows.some(
                                                (row) => row.oldValue !== null,
                                            )
                                        "
                                        class="px-4 py-3 text-left"
                                    >
                                        Previous value
                                    </th>
                                    <th class="px-4 py-3 text-left">
                                        Recorded value
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in detailRows"
                                    :key="row.field"
                                    class="border-t"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ row.field }}
                                    </td>
                                    <td
                                        v-if="
                                            detailRows.some(
                                                (item) =>
                                                    item.oldValue !== null,
                                            )
                                        "
                                        class="px-4 py-3 text-muted-foreground"
                                    >
                                        {{ row.oldValue ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 break-all">
                                        {{ row.newValue }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        {{
                            log.details ||
                            'No additional details were recorded for this event.'
                        }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
