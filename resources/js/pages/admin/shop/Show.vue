<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
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

interface ModuleItem {
    id: number;
    name: string;
    price: string | number;
}

interface LatestOrder {
    public_id: string;
    subscription_plan: string | null;
    expires_at: string | null;
    total_price: string | null;
    status: string;
    billing_months: number;
    payment_method: string | null;
    is_trial: boolean;
    is_upgrade: boolean;
    created_at: string;
    modules: ModuleItem[];
}

interface ShopDetails {
    id: number;
    public_id: string;
    shop_name: string;
    branch_name: string | null;
    phone: string;
    block_street: string | null;
    municipality: string;
    barangay: string;
    postal_code: string;
    latitude: number | null;
    longitude: number | null;
    cover_photo: string | null;
    status: string;
    disable_reason: string | null;
    created_at: string;
    updated_at: string;
    last_activity_at: string | null;
    compliance_status: string;
    health_status: string;
    is_inactive: boolean;
    bir_expiry_date: string | null;
    dti_expiry_date: string | null;
    mayors_expiry_date: string | null;
    sanitary_expiry_date: string | null;
    employees_count: number;
    services_count: number;
    orders_count: number;
    owner: {
        public_id: string;
        name: string;
        email: string;
        email_verified_at: string | null;
        created_at: string;
    } | null;
    latest_order: LatestOrder | null;
    permit_files: {
        bir: string | null;
        dti: string | null;
        mayors: string | null;
        sanitary: string | null;
    };
    permit_submission: {
        order_public_id: string;
        status: string;
        submitted_at: string;
    } | null;
}

const { shop } = defineProps<{ shop: ShopDetails }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
    { title: shop.shop_name, href: `/admin/shop/${shop.public_id}` },
];

const permits = computed(() => [
    {
        key: 'bir',
        label: 'BIR Certificate of Registration',
        description: 'Bureau of Internal Revenue registration',
        required: true,
        expiry: shop.bir_expiry_date,
        file: shop.permit_files.bir,
    },
    {
        key: 'dti',
        label: 'DTI Business Name Registration',
        description: 'Department of Trade and Industry registration',
        required: true,
        expiry: shop.dti_expiry_date,
        file: shop.permit_files.dti,
    },
    {
        key: 'mayors',
        label: "Mayor's Business Permit",
        description: 'Business permit issued by the local government',
        required: true,
        expiry: shop.mayors_expiry_date,
        file: shop.permit_files.mayors,
    },
    {
        key: 'sanitary',
        label: 'Sanitary Permit',
        description: 'Health and sanitation clearance',
        required: false,
        expiry: shop.sanitary_expiry_date,
        file: shop.permit_files.sanitary,
    },
]);

const disableDialogOpen = ref(false);
const disableReason = ref('');

function formatDate(date: string | null) {
    if (!date) return 'Not recorded';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatDateTime(date: string | null) {
    if (!date) return 'Not recorded';
    return new Date(date).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMoney(value: string | number | null) {
    if (value === null) return 'Not recorded';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value));
}

function daysFromToday(date: string) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const target = new Date(date);
    target.setHours(0, 0, 0, 0);
    return Math.round((target.getTime() - today.getTime()) / 86_400_000);
}

function expiryMessage(date: string | null, required = true) {
    if (!date)
        return required ? 'Expiration date is missing' : 'Optional permit';

    const days = daysFromToday(date);
    if (days === 0) return 'Expires today';
    if (days < 0) {
        const elapsed = Math.abs(days);
        return `Expired ${durationLabel(elapsed)} ago`;
    }

    return `Expires in ${durationLabel(days)}`;
}

function durationLabel(totalDays: number) {
    if (totalDays >= 365) {
        const years = Math.floor(totalDays / 365);
        const remainingDays = totalDays % 365;
        const months = Math.floor(remainingDays / 30);
        return [
            `${years} ${years === 1 ? 'year' : 'years'}`,
            months > 0 ? `${months} ${months === 1 ? 'month' : 'months'}` : '',
        ]
            .filter(Boolean)
            .join(', ');
    }

    if (totalDays >= 30) {
        const months = Math.floor(totalDays / 30);
        const days = totalDays % 30;
        return [
            `${months} ${months === 1 ? 'month' : 'months'}`,
            days > 0 ? `${days} ${days === 1 ? 'day' : 'days'}` : '',
        ]
            .filter(Boolean)
            .join(', ');
    }

    return `${totalDays} ${totalDays === 1 ? 'day' : 'days'}`;
}

function expiryClass(date: string | null, required = true) {
    if (!date) {
        return required
            ? 'bg-red-100 text-red-700'
            : 'bg-slate-100 text-slate-600';
    }
    const days = daysFromToday(date);
    if (days < 0) return 'bg-red-100 text-red-700';
    if (days <= 30) return 'bg-amber-100 text-amber-700';
    return 'bg-emerald-100 text-emerald-700';
}

function badgeClass(status: string) {
    const classes: Record<string, string> = {
        active: 'bg-emerald-100 text-emerald-700',
        pending: 'bg-amber-100 text-amber-700',
        disabled: 'bg-slate-200 text-slate-700',
        inactive: 'bg-amber-100 text-amber-700',
        healthy: 'bg-emerald-100 text-emerald-700',
        attention: 'bg-amber-100 text-amber-700',
        critical: 'bg-red-100 text-red-700',
        compliant: 'bg-emerald-100 text-emerald-700',
        expiring: 'bg-amber-100 text-amber-700',
        expired: 'bg-red-100 text-red-700',
        incomplete: 'bg-slate-100 text-slate-600',
        approved: 'bg-emerald-100 text-emerald-700',
        paid: 'bg-emerald-100 text-emerald-700',
    };
    return classes[status] ?? 'bg-slate-100 text-slate-600';
}

function kycUrl(path: string) {
    return `/admin/kyc-file?path=${encodeURIComponent(path)}`;
}

function isImage(path: string) {
    return /\.(jpg|jpeg|png)$/i.test(path);
}

function mapUrl() {
    return `https://www.google.com/maps?q=${shop.latitude},${shop.longitude}`;
}

function confirmDisable() {
    router.post(
        `/admin/shop/${shop.public_id}/disable`,
        { reason: disableReason.value },
        {
            onSuccess: () => {
                disableDialogOpen.value = false;
                disableReason.value = '';
            },
        },
    );
}

function enableShop() {
    router.post(`/admin/shop/${shop.public_id}/enable`);
}
</script>

<template>
    <Head :title="`${shop.shop_name} — Details`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Details">
        <div class="space-y-6 px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Button variant="outline" @click="router.visit('/admin/shop')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Shops
                </Button>

                <div class="flex flex-wrap gap-2">
                    <Button
                        v-if="shop.status !== 'disabled'"
                        variant="outline"
                        class="border-amber-300 text-amber-700 hover:bg-amber-50"
                        @click="disableDialogOpen = true"
                    >
                        <ShieldOff class="mr-2 h-4 w-4" />
                        Disable Shop
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        class="border-emerald-300 text-emerald-700 hover:bg-emerald-50"
                        @click="enableShop"
                    >
                        <ShieldCheck class="mr-2 h-4 w-4" />
                        Enable Shop
                    </Button>
                    <Button
                        @click="
                            router.visit(`/admin/shop/${shop.public_id}/edit`)
                        "
                    >
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Shop
                    </Button>
                </div>
            </div>

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
        </div>

        <AlertDialog
            :open="disableDialogOpen"
            @update:open="disableDialogOpen = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Disable Shop</AlertDialogTitle>
                    <AlertDialogDescription>
                        Disable <strong>{{ shop.shop_name }}</strong
                        >? The owner and staff will lose access until it is
                        enabled again.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <Textarea
                    v-model="disableReason"
                    placeholder="Reason for disabling this shop"
                    rows="3"
                />
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        :disabled="!disableReason.trim()"
                        class="bg-amber-600 hover:bg-amber-700"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>

<style scoped>
.detail-label {
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    line-height: 1rem;
    color: var(--muted-foreground);
}
</style>
