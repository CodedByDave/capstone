<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    CalendarDays,
    CircleUserRound,
    Mail,
    Shield,
    Store,
} from 'lucide-vue-next';

interface Shop {
    id: number;
    shop_name: string;
    branch_name?: string | null;
    status?: string | null;
}

interface UserDetails {
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    shop: Shop | null;
}

const { user } = defineProps<{ user: UserDetails }>();

const roleLabels: Record<string, string> = {
    owner: 'Shop Owner',
    manager: 'Manager',
    staff: 'Staff',
    user: 'Customer',
};

const roleClasses: Record<string, string> = {
    owner: 'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300',
    manager: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300',
    staff: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    user: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
};

function formatDateTime(value: string | null) {
    if (!value) return 'Not available';

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(value));
}

function initials(name: string) {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');
}
</script>

<template>
    <Head :title="`${user.name} - User Details`" />

    <AdminLayout title="User Details">
        <div class="mx-auto max-w-5xl space-y-6">
            <Button variant="outline" @click="router.visit('/admin/users')">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Users
            </Button>

            <Card>
                <CardContent class="p-6">
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xl font-semibold text-primary"
                            >
                                {{ initials(user.name) }}
                            </div>
                            <div>
                                <h1
                                    class="text-2xl font-semibold tracking-tight"
                                >
                                    {{ user.name }}
                                </h1>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    roleClasses[user.role] ?? roleClasses.user
                                "
                            >
                                {{ roleLabels[user.role] ?? user.role }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    user.email_verified_at
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                "
                            >
                                {{
                                    user.email_verified_at
                                        ? 'Verified account'
                                        : 'Unverified account'
                                }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <CircleUserRound class="h-5 w-5 text-primary" />
                            Account Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5 text-sm">
                        <div class="flex items-start gap-3">
                            <Mail
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Email address
                                </p>
                                <p class="mt-1 font-medium break-all">
                                    {{ user.email }}
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
                                    Account role
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ roleLabels[user.role] ?? user.role }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <BadgeCheck
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                >
                                    Email verification
                                </p>
                                <p class="mt-1 font-medium">
                                    {{
                                        user.email_verified_at
                                            ? formatDateTime(
                                                  user.email_verified_at,
                                              )
                                            : 'Not yet verified'
                                    }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <CalendarDays class="h-5 w-5 text-primary" />
                            Activity
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5 text-sm">
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Joined
                            </p>
                            <p class="mt-1 font-medium">
                                {{ formatDateTime(user.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Last updated
                            </p>
                            <p class="mt-1 font-medium">
                                {{ formatDateTime(user.updated_at) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="user.shop">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-lg">
                        <Store class="h-5 w-5 text-primary" />
                        Associated Shop
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div
                        class="grid gap-5 text-sm sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Shop name
                            </p>
                            <p class="mt-1 font-medium">
                                {{ user.shop.shop_name }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Branch
                            </p>
                            <p class="mt-1 font-medium">
                                {{ user.shop.branch_name || 'Not specified' }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground uppercase"
                            >
                                Status
                            </p>
                            <p class="mt-1 font-medium capitalize">
                                {{ user.shop.status || 'Not specified' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
