<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

import { StatusPill, type StatusTone } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Lock } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const { shop, overview } = defineProps<{
    shop: {
        id: number;
        public_id: string;
        shop_name: string;
        phone: string;
        block_street: string;
        municipality: string;
        barangay: string;
        postal_code: string;
        status: string;
        bir_expiry_date: string | null;
        mayors_expiry_date: string | null;
        dti_expiry_date: string | null;
        sanitary_expiry_date: string | null;
        owner: { name: string; email: string };
    };
    overview: {
        health_status: string;
        compliance_status: string;
        is_inactive: boolean;
        last_activity_at: string | null;
        subscription_plan: string | null;
        expires_at: string | null;
        is_expired: boolean;
        created_at: string;
        owner: { name: string; email: string } | null;
    };
}>();

const toneByState: Record<string, StatusTone> = {
    healthy: 'success',
    compliant: 'success',
    attention: 'warning',
    expiring: 'warning',
    incomplete: 'neutral',
    critical: 'danger',
    expired: 'danger',
};

function formatDate(value: string | null) {
    if (!value) return 'Not provided';
    return new Date(value).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function relativeDay(value: string | null) {
    if (!value) return 'No activity yet';
    const days = Math.floor(
        (new Date().setHours(0, 0, 0, 0) -
            new Date(value).setHours(0, 0, 0, 0)) /
            86_400_000,
    );
    if (days <= 0) return 'Today';
    if (days === 1) return 'Yesterday';
    return `${days} days ago`;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
    { title: shop.shop_name, href: `/admin/shop/${shop.public_id}` },
    { title: 'Edit', href: `/admin/shop/${shop.public_id}/edit` },
];

// Admins may only change the platform status; everything else is owned by the shop.
const form = useForm({
    status: shop.status,
});

const isDirty = computed(() => form.status !== shop.status);

const statusOptions = [
    {
        value: 'active',
        label: 'Active',
        hint: 'Shop can operate and take orders.',
    },
    {
        value: 'pending',
        label: 'Pending',
        hint: 'Awaiting review or approval.',
    },
    {
        value: 'disabled',
        label: 'Disabled',
        hint: 'Shop is blocked from operating.',
    },
];

const ownerFields = [
    { label: 'Shop name', value: shop.shop_name },
    { label: 'Owner', value: shop.owner.name },
    { label: 'Owner email', value: shop.owner.email },
    { label: 'Phone number', value: shop.phone },
];

const addressFields = [
    { label: 'Block / street', value: shop.block_street },
    { label: 'Barangay', value: shop.barangay },
    { label: 'Municipality', value: shop.municipality },
    { label: 'Postal code', value: shop.postal_code },
];

const permitFields = [
    {
        label: 'BIR registration expiry',
        value: shop.bir_expiry_date,
        required: true,
    },
    {
        label: "Mayor's permit expiry",
        value: shop.mayors_expiry_date,
        required: true,
    },
    {
        label: 'DTI registration expiry',
        value: shop.dti_expiry_date,
        required: true,
    },
    {
        label: 'Sanitary permit expiry (optional)',
        value: shop.sanitary_expiry_date,
        required: false,
    },
];

function submit() {
    form.put(`/admin/shop/${shop.public_id}`, {
        onSuccess: () => {
            toast.success('Shop updated successfully.', { autoClose: 3000 });
            router.visit(`/admin/shop/${shop.public_id}`);
        },
        onError: () => {
            toast.error('Failed to update shop. Please check the form.', {
                autoClose: 4000,
            });
        },
    });
}

function back() {
    router.visit(`/admin/shop/${shop.public_id}`);
}

const kpiLabel =
    'text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase';
const fieldLabel = 'text-xs font-medium text-muted-foreground';
</script>

<template>
    <Head :title="`Edit — ${shop.shop_name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Edit Shop">
        <div class="px-6 sm:px-10 lg:px-[100px]">
            <button
                type="button"
                class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-md text-sm text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                @click="back"
            >
                <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                Back to details
            </button>

            <h2 class="text-3xl font-bold tracking-tight">
                Edit {{ shop.shop_name }}
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Manage the platform status of this shop.
            </p>

            <!-- Read-only KPI tiles -->
            <dl
                class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5"
                aria-label="Shop overview"
            >
                <div class="rounded-2xl border bg-card p-5">
                    <dt :class="kpiLabel">Health</dt>
                    <dd class="mt-3">
                        <StatusPill
                            :tone="
                                toneByState[overview.health_status] ?? 'neutral'
                            "
                        >
                            <span class="capitalize">{{
                                overview.health_status
                            }}</span>
                        </StatusPill>
                    </dd>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <dt :class="kpiLabel">Compliance</dt>
                    <dd class="mt-3">
                        <StatusPill
                            :tone="
                                toneByState[overview.compliance_status] ??
                                'neutral'
                            "
                        >
                            <span class="capitalize">{{
                                overview.compliance_status
                            }}</span>
                        </StatusPill>
                    </dd>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <dt :class="kpiLabel">Subscription</dt>
                    <dd class="mt-3 text-lg font-semibold">
                        {{ overview.subscription_plan ?? 'No plan' }}
                        <span
                            class="mt-1 block text-sm font-normal"
                            :class="
                                overview.is_expired
                                    ? 'text-red-500'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ overview.is_expired ? 'Expired' : 'Renews' }}
                            {{ formatDate(overview.expires_at) }}
                        </span>
                    </dd>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <dt :class="kpiLabel">Last activity</dt>
                    <dd class="mt-3 text-lg font-semibold">
                        {{ formatDate(overview.last_activity_at) }}
                        <span
                            class="mt-1 block text-sm font-normal"
                            :class="
                                overview.is_inactive
                                    ? 'text-amber-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ relativeDay(overview.last_activity_at) }}
                        </span>
                    </dd>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <dt :class="kpiLabel">Registered</dt>
                    <dd class="mt-3 text-lg font-semibold">
                        {{ formatDate(overview.created_at) }}
                        <span
                            class="mt-1 block text-sm font-normal text-muted-foreground"
                        >
                            {{ relativeDay(overview.created_at) }}
                        </span>
                    </dd>
                </div>
            </dl>

            <div
                class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_300px]"
            >
                <form class="space-y-8" @submit.prevent="submit">
                    <!-- Editable: status -->
                    <section aria-labelledby="status-heading">
                        <h3 id="status-heading" class="text-xl font-semibold">
                            Shop status
                            <span class="text-red-500" aria-hidden="true"
                                >*</span
                            >
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            This is the only setting an administrator can
                            change.
                        </p>
                        <div
                            class="mt-5 grid gap-3 sm:grid-cols-3"
                            role="radiogroup"
                            aria-labelledby="status-heading"
                        >
                            <button
                                v-for="option in statusOptions"
                                :key="option.value"
                                type="button"
                                role="radio"
                                :aria-checked="form.status === option.value"
                                class="flex min-h-11 items-start gap-3 rounded-xl border p-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    form.status === option.value
                                        ? 'border-primary bg-primary/10'
                                        : 'hover:bg-muted/50'
                                "
                                @click="form.status = option.value"
                            >
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        form.status === option.value
                                            ? 'border-primary'
                                            : 'border-muted-foreground'
                                    "
                                    aria-hidden="true"
                                >
                                    <span
                                        v-if="form.status === option.value"
                                        class="h-2 w-2 rounded-full bg-primary"
                                    />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold">{{
                                        option.label
                                    }}</span>
                                    <span
                                        class="mt-1 block text-sm text-muted-foreground"
                                        >{{ option.hint }}</span
                                    >
                                </span>
                            </button>
                        </div>
                        <p
                            v-if="form.errors.status"
                            class="mt-2 text-xs text-red-500"
                        >
                            {{ form.errors.status }}
                        </p>
                    </section>

                    <!-- Note: why the rest is locked -->
                    <p
                        class="flex items-start gap-3 text-sm text-muted-foreground"
                        role="note"
                    >
                        <Lock
                            class="mt-0.5 h-4 w-4 shrink-0"
                            aria-hidden="true"
                        />
                        Everything below belongs to the shop and is read-only
                        here. The owner updates it from their own account, and
                        the subscription is managed through Orders.
                    </p>

                    <section
                        class="border-t pt-8"
                        aria-labelledby="identity-heading"
                    >
                        <h3 id="identity-heading" class="text-xl font-semibold">
                            Shop &amp; owner
                        </h3>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div
                                v-for="field in ownerFields"
                                :key="field.label"
                            >
                                <dt :class="fieldLabel">{{ field.label }}</dt>
                                <dd
                                    class="mt-1 text-base font-medium break-words"
                                >
                                    {{ field.value || 'Not provided' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section
                        class="border-t pt-8"
                        aria-labelledby="address-heading"
                    >
                        <h3 id="address-heading" class="text-xl font-semibold">
                            Address
                        </h3>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div
                                v-for="field in addressFields"
                                :key="field.label"
                            >
                                <dt :class="fieldLabel">{{ field.label }}</dt>
                                <dd
                                    class="mt-1 text-base font-medium break-words"
                                >
                                    {{ field.value || 'Not provided' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section
                        class="border-t pt-8"
                        aria-labelledby="permits-heading"
                    >
                        <h3 id="permits-heading" class="text-xl font-semibold">
                            Permits &amp; registrations
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Expiry dates entered by the owner. Compliance stays
                            incomplete until all three required permits are on
                            file.
                        </p>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div
                                v-for="field in permitFields"
                                :key="field.label"
                            >
                                <dt :class="fieldLabel">{{ field.label }}</dt>
                                <dd
                                    class="mt-1 text-base font-medium"
                                    :class="
                                        !field.value && field.required
                                            ? 'text-amber-600 dark:text-amber-400'
                                            : !field.value
                                              ? 'font-normal text-muted-foreground'
                                              : ''
                                    "
                                >
                                    {{ formatDate(field.value) }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                </form>

                <!-- Single sticky side panel -->
                <aside class="lg:sticky lg:top-4 lg:self-start">
                    <div class="rounded-2xl border bg-card p-5">
                        <p :class="kpiLabel">Status change</p>
                        <p
                            class="mt-3 text-sm font-semibold"
                            aria-live="polite"
                        >
                            {{
                                isDirty
                                    ? 'You have unsaved changes'
                                    : 'No changes yet'
                            }}
                        </p>
                        <div class="mt-5 flex flex-col gap-2">
                            <Button
                                :disabled="!isDirty || form.processing"
                                @click="submit"
                            >
                                {{
                                    form.processing
                                        ? 'Saving...'
                                        : 'Save changes'
                                }}
                            </Button>
                            <Button variant="ghost" @click="back"
                                >Cancel</Button
                            >
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
