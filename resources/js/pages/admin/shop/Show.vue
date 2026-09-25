<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref } from 'vue'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Pencil, ShieldOff, ShieldCheck } from 'lucide-vue-next'

import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
<<<<<<< HEAD
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog'
=======
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeft,
    ExternalLink,
    FileText,
    Pencil,
    ShieldCheck,
    ShieldOff,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

type LatestOrder = {
    subscription_plan: string | null
    expires_at: string | null
    total_price: string | null
    status: string | null
    created_at: string | null
} | null

const { shop } = defineProps<{
    shop: {
        id: number
        shop_name: string
        branch_name: string | null
        phone: string
        block_street: string
        municipality: string
        barangay: string
        postal_code: string
        status: string
        disable_reason: string | null
        created_at: string
        updated_at: string
        owner: { name: string; email: string }
        latest_order: LatestOrder
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
    { title: shop.shop_name, href: `/admin/shop/${shop.id}` },
]

const planStyles: Record<string, { label: string; class: string }> = {
    monthly:       { label: 'Monthly',      class: 'bg-blue-100 text-blue-700' },
    quarterly:     { label: 'Quarterly',    class: 'bg-purple-100 text-purple-700' },
    semi_annually: { label: 'Semi-Annual',  class: 'bg-green-100 text-green-700' },
    annually:      { label: 'Annually',     class: 'bg-amber-100 text-amber-700' },
}

function getPlanBadge(order: LatestOrder) {
    if (!order?.subscription_plan || order.status !== 'paid') {
        return { label: 'No Active Plan', class: 'bg-gray-100 text-gray-400' }
    }
    return planStyles[order.subscription_plan] ?? { label: order.subscription_plan, class: 'bg-gray-100 text-gray-500' }
}

function isExpired(expiresAt: string | null) {
    if (!expiresAt) return false
    return new Date(expiresAt) < new Date()
}

function formatDate(date: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
}

function formatDateTime(date: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const disableDialogOpen = ref(false)
const disableReason = ref('')

function confirmDisable() {
    router.post(`/admin/shop/${shop.id}/disable`, { reason: disableReason.value }, {
        onSuccess: () => {
            disableDialogOpen.value = false
            disableReason.value = ''
        },
    })
}

function enableShop() {
    router.post(`/admin/shop/${shop.id}/enable`)
}
</script>

<template>
    <Head :title="`${shop.shop_name} — Details`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Details">

        <div class="flex items-center justify-between mb-6">
            <Button variant="outline" @click="router.visit('/admin/shop')">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Shops
            </Button>

            <div class="flex gap-2">
                <!-- Disable -->
                <template v-if="shop.status !== 'disabled'">
                    <Button
                        variant="outline"
                        class="border-orange-400 text-orange-600 hover:bg-orange-50"
                        @click="disableDialogOpen = true"
                    >
                        <ShieldOff class="h-4 w-4 mr-2" />
                        Disable Shop
                    </Button>
                </template>

                <!-- Enable -->
                <template v-else>
                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <Button variant="outline" class="border-green-500 text-green-600 hover:bg-green-50">
                                <ShieldCheck class="h-4 w-4 mr-2" />
                                Enable Shop
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Enable Shop</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Re-enable <strong>{{ shop.shop_name }}</strong>?
                                    They will regain full access to the system.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="enableShop">Enable</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </template>

                <Button @click="router.visit(`/admin/shop/${shop.id}/edit`)">
                    <Pencil class="h-4 w-4 mr-2" />
                    Edit Shop
                </Button>
            </div>
<<<<<<< HEAD
=======

            <div
                v-if="shop.status === 'disabled'"
                class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            >
                <ShieldOff class="mt-0.5 h-4 w-4 shrink-0" />
                <div>
                    <p class="font-semibold">This shop is disabled.</p>
                    <p v-if="shop.disable_reason" class="mt-0.5">
                        Reason: {{ shop.disable_reason }}
                    </p>
                </div>
            </div>

            <Card>
                <CardContent class="p-5">
                    <div class="flex flex-col gap-5 sm:flex-row">
                        <div
                            class="flex h-24 w-full shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted sm:w-32"
                        >
                            <img
                                v-if="shop.cover_photo"
                                :src="shop.cover_photo"
                                :alt="shop.shop_name"
                                class="h-full w-full object-cover"
                            />
                            <Store
                                v-else
                                class="h-10 w-10 text-muted-foreground/40"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl font-bold">
                                    {{ shop.shop_name }}
                                </h1>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="badgeClass(shop.status)"
                                >
                                    {{ shop.status }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ shop.branch_name || 'Main shop' }} ·
                                {{ shop.barangay }}, {{ shop.municipality }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="badgeClass(shop.health_status)"
                                >
                                    Health: {{ shop.health_status }}
                                </span>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="badgeClass(shop.compliance_status)"
                                >
                                    Compliance: {{ shop.compliance_status }}
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 sm:w-72">
                            <div class="rounded-lg bg-muted/50 p-3 text-center">
                                <p class="text-xl font-bold">
                                    {{ shop.employees_count }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Employees
                                </p>
                            </div>
                            <div class="rounded-lg bg-muted/50 p-3 text-center">
                                <p class="text-xl font-bold">
                                    {{ shop.services_count }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Services
                                </p>
                            </div>
                            <div class="rounded-lg bg-muted/50 p-3 text-center">
                                <p class="text-xl font-bold">
                                    {{ shop.orders_count }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Orders
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            Shop Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <div>
                            <p class="detail-label">Shop name</p>
                            <p class="font-medium">{{ shop.shop_name }}</p>
                        </div>
                        <div>
                            <p class="detail-label">Branch</p>
                            <p class="font-medium">
                                {{ shop.branch_name || 'Main shop' }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Phone</p>
                            <p class="flex items-center gap-2 font-medium">
                                {{ shop.phone }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Registered</p>
                            <p class="font-medium">
                                {{ formatDateTime(shop.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Last updated</p>
                            <p class="font-medium">
                                {{ formatDateTime(shop.updated_at) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            Owner Account
                        </CardTitle>
                    </CardHeader>
                    <CardContent v-if="shop.owner" class="space-y-4 text-sm">
                        <div>
                            <p class="detail-label">Owner</p>
                            <p class="font-medium">{{ shop.owner.name }}</p>
                        </div>
                        <div>
                            <p class="detail-label">Email</p>
                            <p
                                class="flex items-center gap-2 font-medium break-all"
                            >
                                {{ shop.owner.email }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Email status</p>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    shop.owner.email_verified_at
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                {{
                                    shop.owner.email_verified_at
                                        ? 'Verified'
                                        : 'Unverified'
                                }}
                            </span>
                        </div>
                        <div>
                            <p class="detail-label">Account created</p>
                            <p class="font-medium">
                                {{ formatDateTime(shop.owner.created_at) }}
                            </p>
                        </div>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="
                                router.visit(
                                    `/admin/users/${shop.owner?.public_id}`,
                                )
                            "
                            class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                        >
                            View owner account
                        </Button>
                    </CardContent>
                    <CardContent v-else class="text-sm text-muted-foreground">
                        No owner is assigned to this shop.
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            Location and Activity
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <div>
                            <p class="detail-label">Complete address</p>
                            <p class="font-medium">
                                {{
                                    [
                                        shop.block_street,
                                        shop.barangay,
                                        shop.municipality,
                                        shop.postal_code,
                                    ]
                                        .filter(Boolean)
                                        .join(', ')
                                }}
                            </p>
                        </div>
                        <div
                            v-if="
                                shop.latitude !== null &&
                                shop.longitude !== null
                            "
                        >
                            <p class="detail-label">GPS coordinates</p>
                            <p class="font-medium">
                                {{ shop.latitude }}, {{ shop.longitude }}
                            </p>
                            <a
                                :href="mapUrl()"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline"
                            >
                                Open in Maps
                                <ExternalLink class="h-3 w-3" />
                            </a>
                        </div>
                        <div>
                            <p class="detail-label">Last shop activity</p>
                            <p
                                class="flex items-center gap-2 font-medium"
                                :class="
                                    shop.is_inactive
                                        ? 'text-amber-700'
                                        : 'text-emerald-700'
                                "
                            >
                                <Activity class="h-3.5 w-3.5" />
                                {{ formatDateTime(shop.last_activity_at) }}
                            </p>
                            <p
                                v-if="shop.is_inactive"
                                class="mt-1 text-xs text-amber-600"
                            >
                                No recorded activity within the last 30 days.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <CardTitle>Permits and Compliance</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Uploaded business documents and their expiration
                                timelines.
                            </p>
                        </div>
                        <div
                            v-if="shop.permit_submission"
                            class="text-xs text-muted-foreground"
                        >
                            Submitted
                            {{
                                formatDateTime(
                                    shop.permit_submission.submitted_at,
                                )
                            }}
                            ·
                            <span class="capitalize">
                                {{ shop.permit_submission.status }}
                            </span>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="permit in permits"
                            :key="permit.key"
                            class="overflow-hidden rounded-xl border"
                        >
                            <div
                                class="flex items-start justify-between gap-3 border-b bg-muted/30 p-4"
                            >
                                <div>
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p class="font-semibold">
                                            {{ permit.label }}
                                        </p>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                            :class="
                                                permit.required
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-slate-100 text-slate-600'
                                            "
                                        >
                                            {{
                                                permit.required
                                                    ? 'Required'
                                                    : 'Optional'
                                            }}
                                        </span>
                                    </div>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ permit.description }}
                                    </p>
                                </div>
                                <a
                                    v-if="permit.file"
                                    :href="kycUrl(permit.file)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex shrink-0 items-center gap-1 text-xs font-medium text-blue-600 hover:underline"
                                >
                                    Open
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                            </div>

                            <div
                                class="grid gap-4 p-4 sm:grid-cols-[140px_1fr]"
                            >
                                <div
                                    class="flex h-32 items-center justify-center overflow-hidden rounded-lg border bg-muted/20"
                                >
                                    <img
                                        v-if="
                                            permit.file && isImage(permit.file)
                                        "
                                        :src="kycUrl(permit.file)"
                                        :alt="permit.label"
                                        class="h-full w-full object-contain"
                                    />
                                    <div
                                        v-else-if="permit.file"
                                        class="text-center text-muted-foreground"
                                    >
                                        <FileText
                                            class="mx-auto h-9 w-9 opacity-40"
                                        />
                                        <p class="mt-1 text-xs">PDF document</p>
                                    </div>
                                    <div
                                        v-else
                                        class="text-center text-muted-foreground"
                                    >
                                        <FileText
                                            class="mx-auto h-9 w-9 opacity-20"
                                        />
                                        <p class="mt-1 text-xs">Not uploaded</p>
                                    </div>
                                </div>

                                <div class="space-y-3 text-sm">
                                    <div>
                                        <p class="detail-label">Document</p>
                                        <p
                                            class="font-medium"
                                            :class="
                                                permit.file
                                                    ? 'text-emerald-700'
                                                    : permit.required
                                                      ? 'text-red-700'
                                                      : 'text-muted-foreground'
                                            "
                                        >
                                            {{
                                                permit.file
                                                    ? 'Uploaded'
                                                    : permit.required
                                                      ? 'Missing'
                                                      : 'Not submitted'
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="detail-label">
                                            Expiration date
                                        </p>
                                        <p class="font-medium">
                                            {{ formatDate(permit.expiry) }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="
                                            expiryClass(
                                                permit.expiry,
                                                permit.required,
                                            )
                                        "
                                    >
                                        {{
                                            expiryMessage(
                                                permit.expiry,
                                                permit.required,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        Subscription Details
                    </CardTitle>
                </CardHeader>
                <CardContent v-if="shop.latest_order" class="space-y-5">
                    <div
                        class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <p class="detail-label">Plan</p>
                            <p class="font-semibold">
                                {{
                                    shop.latest_order.subscription_plan ||
                                    'No plan recorded'
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Status</p>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="badgeClass(shop.latest_order.status)"
                            >
                                {{ shop.latest_order.status }}
                            </span>
                        </div>
                        <div>
                            <p class="detail-label">Amount</p>
                            <p class="font-semibold">
                                {{ formatMoney(shop.latest_order.total_price) }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Billing period</p>
                            <p class="font-semibold">
                                {{ shop.latest_order.billing_months }} month{{
                                    shop.latest_order.billing_months === 1
                                        ? ''
                                        : 's'
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Subscribed on</p>
                            <p class="font-medium">
                                {{
                                    formatDateTime(shop.latest_order.created_at)
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Expiration date</p>
                            <p class="font-medium">
                                {{ formatDate(shop.latest_order.expires_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="detail-label">Time remaining</p>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    expiryClass(
                                        shop.latest_order.expires_at,
                                        true,
                                    )
                                "
                            >
                                {{
                                    expiryMessage(
                                        shop.latest_order.expires_at,
                                        true,
                                    )
                                }}
                            </span>
                        </div>
                        <div>
                            <p class="detail-label">Payment method</p>
                            <p class="font-medium capitalize">
                                {{
                                    shop.latest_order.payment_method?.replace(
                                        '_',
                                        ' ',
                                    ) || 'Not recorded'
                                }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="detail-label mb-2">Included modules</p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="module in shop.latest_order.modules"
                                :key="module.id"
                                class="rounded-full border bg-muted/30 px-3 py-1 text-xs font-medium"
                            >
                                {{ module.name }}
                            </span>
                            <span
                                v-if="!shop.latest_order.modules.length"
                                class="text-sm text-muted-foreground"
                            >
                                No modules recorded.
                            </span>
                        </div>
                    </div>

                    <Button
                        size="sm"
                        variant="outline"
                        @click="
                            router.visit(
                                `/admin/orders/${shop.latest_order?.public_id}`,
                            )
                        "
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                    >
                        View subscription order
                    </Button>
                </CardContent>
                <CardContent v-else class="text-sm text-muted-foreground">
                    No paid or approved subscription is recorded for this shop.
                </CardContent>
            </Card>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
        </div>

        <!-- Disabled Banner -->
        <div
            v-if="shop.status === 'disabled'"
            class="mb-4 flex items-start gap-3 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800"
        >
            <ShieldOff class="h-4 w-4 mt-0.5 shrink-0" />
            <div>
                <p class="font-semibold">This shop has been disabled.</p>
                <p v-if="shop.disable_reason" class="mt-0.5 text-orange-700">
                    Reason: {{ shop.disable_reason }}
                </p>
            </div>
        </div>

        <!-- Shop Details -->
        <Card class="mb-6">
            <CardHeader>
                <CardTitle>Shop Information</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Shop Name</p>
                        <p class="font-medium">{{ shop.shop_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Branch Name</p>
                        <p class="font-medium">{{ shop.branch_name ?? '—' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Owner</p>
                        <p class="font-medium">{{ shop.owner.name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Email</p>
                        <p class="font-medium">{{ shop.owner.email }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Phone</p>
                        <p class="font-medium">{{ shop.phone }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Status</p>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold rounded-full text-white"
                            :class="{
                                'bg-green-500':  shop.status === 'active',
                                'bg-red-500':    shop.status === 'inactive',
                                'bg-yellow-500': shop.status === 'pending',
                                'bg-gray-500':   shop.status === 'disabled',
                            }"
                        >
                            {{ shop.status }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Block / Street</p>
                        <p class="font-medium">{{ shop.block_street }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Barangay</p>
                        <p class="font-medium">{{ shop.barangay }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Municipality</p>
                        <p class="font-medium">{{ shop.municipality }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Postal Code</p>
                        <p class="font-medium">{{ shop.postal_code }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Registered</p>
                        <p class="font-medium">{{ formatDateTime(shop.created_at) }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Last Updated</p>
                        <p class="font-medium">{{ formatDateTime(shop.updated_at) }}</p>
                    </div>

                    <!-- Disable Reason -->
                    <div v-if="shop.disable_reason" class="space-y-1 md:col-span-2">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Disable Reason</p>
                        <p class="font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded px-3 py-2">
                            {{ shop.disable_reason }}
                        </p>
                    </div>

                </div>
            </CardContent>
        </Card>

        <!-- Subscription Card -->
        <Card>
            <CardHeader>
                <CardTitle>Subscription</CardTitle>
            </CardHeader>
            <CardContent>
                <template v-if="shop.latest_order && shop.latest_order.status === 'paid'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Plan</p>
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getPlanBadge(shop.latest_order).class"
                            >
                                {{ getPlanBadge(shop.latest_order).label }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Amount Paid</p>
                            <p class="font-medium">
                                ₱{{ Number(shop.latest_order.total_price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Subscribed On</p>
                            <p class="font-medium">{{ formatDateTime(shop.latest_order.created_at) }}</p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Expires On</p>
                            <p
                                class="font-medium"
                                :class="isExpired(shop.latest_order.expires_at) ? 'text-red-600' : 'text-gray-900'"
                            >
                                {{ formatDate(shop.latest_order.expires_at) }}
                                <span
                                    v-if="isExpired(shop.latest_order.expires_at)"
                                    class="ml-2 text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold"
                                >
                                    Expired
                                </span>
                            </p>
                        </div>

                    </div>
                </template>

                <template v-else>
                    <div class="flex flex-col items-center justify-center py-8 text-center text-muted-foreground">
                        <p class="text-sm font-medium">No active subscription found.</p>
                        <p class="text-xs mt-1">This shop has not completed a paid order yet.</p>
                    </div>
                </template>
            </CardContent>
        </Card>

        <!-- Disable Dialog -->
        <AlertDialog :open="disableDialogOpen" @update:open="disableDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="flex items-center gap-2 text-orange-600">
                        <ShieldOff class="h-5 w-5" />
                        Disable Shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        You are about to disable <strong>{{ shop.shop_name }}</strong>.
                        The owner will lose access to the system. Please provide a reason.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="mt-2">
                    <label class="text-sm font-medium">Reason for disabling</label>
                    <Textarea
                        v-model="disableReason"
                        class="mt-1"
                        placeholder="e.g. Violation of terms of service, fraudulent activity, etc."
                        rows="3"
                    />
                </div>
                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel @click="disableDialogOpen = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-orange-600 text-white hover:bg-orange-700"
                        :disabled="!disableReason.trim()"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
