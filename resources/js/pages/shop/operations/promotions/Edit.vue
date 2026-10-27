<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, BadgeDollarSign, Loader2, Percent } from 'lucide-vue-next';
import { computed } from 'vue';

interface Promotion {
    id: number;
    name: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: string;
    min_order_amount: string | null;
    is_active: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

const props = defineProps<{ promotion: Promotion }>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/promos' : '/staff/operations/promos',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Operations', href: '/shop/operations/orders' },
    { title: 'Promotions', href: '/shop/operations/promos' },
    { title: 'Edit Promotion', href: '#' },
];

const form = useForm({
    name: props.promotion.name,
    description: props.promotion.description ?? '',
    type: props.promotion.type,
    value: props.promotion.value as string | number,
    min_order_amount: (props.promotion.min_order_amount ?? '') as
        | string
        | number,
    is_active: props.promotion.is_active,
    starts_at: props.promotion.starts_at ?? '',
    ends_at: props.promotion.ends_at ?? '',
});

const isPercentage = computed(() => form.type === 'percentage');

const onTypeChange = () => {
    form.value = '';
};

const submit = () => form.put(`${base.value}/${props.promotion.id}`);

// ─── Summary panel ────────────────────────────────────────────────────────────

const peso = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatDate = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

const discountText = computed(() => {
    if (form.value === '') return '—';

    return isPercentage.value
        ? `${form.value}% off`
        : `${peso(form.value)} off`;
});

const validityText = computed(() => {
    if (!form.starts_at && !form.ends_at) return 'No date restriction';
    if (form.starts_at && form.ends_at) {
        return `${formatDate(form.starts_at)} to ${formatDate(form.ends_at)}`;
    }

    return form.starts_at
        ? `From ${formatDate(form.starts_at)}`
        : `Until ${formatDate(form.ends_at)}`;
});

const saveStatus = computed(() => {
    if (form.isDirty) return 'You have unsaved changes';

    return form.recentlySuccessful ? 'Changes saved' : 'No changes yet';
});
</script>

<template>
    <Head title="Edit Promotion" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Promotion">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span>Operations</span>
                        <span aria-hidden="true">/</span>
                        <Link :href="base" class="hover:text-foreground"
                            >Promotions</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground"
                            >Edit promotion</span
                        >
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Edit promotion
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update the details for "{{ promotion.name }}".
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="router.visit(base)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to promotions
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Form -->
                <div class="space-y-8">
                    <!-- ── Basic Information ──────────────────── -->
                    <section>
                        <h3 class="text-base font-semibold">
                            Basic information
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Name the promotion and set who can use it.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1 sm:col-span-5">
                                <label class="text-sm font-medium" for="name"
                                    >Promotion Name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Summer Sale, Senior Discount"
                                    :class="{
                                        'border-red-500': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label
                                    class="text-sm font-medium"
                                    for="min-order"
                                    >Minimum Order Amount (₱)</label
                                >
                                <Input
                                    id="min-order"
                                    v-model="form.min_order_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Leave blank for no minimum"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Order must reach this amount before promo
                                    applies.
                                </p>
                            </div>

                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label class="text-sm font-medium"
                                    >Status</label
                                >
                                <div class="flex h-10 items-center gap-3">
                                    <button
                                        type="button"
                                        role="switch"
                                        :aria-checked="form.is_active"
                                        aria-label="Promotion is active"
                                        @click="
                                            form.is_active = !form.is_active
                                        "
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        :class="
                                            form.is_active
                                                ? 'bg-emerald-500'
                                                : 'bg-gray-300'
                                        "
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                                            :class="
                                                form.is_active
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1'
                                            "
                                        />
                                    </button>
                                    <span
                                        class="text-sm"
                                        :class="
                                            form.is_active
                                                ? 'font-medium text-emerald-600'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{
                                            form.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-span-12 space-y-1">
                                <label
                                    class="text-sm font-medium"
                                    for="description"
                                    >Description
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Describe what this promotion is for…"
                                    rows="2"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- ── Discount Type ──────────────────────── -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Discount</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Choose how much is taken off the order.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <!-- Type Selector -->
                            <div class="col-span-12 space-y-2">
                                <label class="text-sm font-medium"
                                    >Discount Type
                                    <span class="text-red-500">*</span></label
                                >
                                <div
                                    class="flex gap-3"
                                    role="radiogroup"
                                    aria-label="Discount type"
                                >
                                    <button
                                        type="button"
                                        role="radio"
                                        :aria-checked="isPercentage"
                                        @click="
                                            form.type = 'percentage';
                                            onTypeChange();
                                        "
                                        class="flex flex-1 items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none sm:flex-none"
                                        :class="
                                            isPercentage
                                                ? 'border-violet-500 bg-violet-50 text-violet-700'
                                                : 'border-gray-200 hover:border-gray-300'
                                        "
                                    >
                                        <Percent class="h-5 w-5 shrink-0" />
                                        <div>
                                            <p class="text-sm font-semibold">
                                                Percentage
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                e.g. 10% off the total
                                            </p>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        role="radio"
                                        :aria-checked="!isPercentage"
                                        @click="
                                            form.type = 'fixed';
                                            onTypeChange();
                                        "
                                        class="flex flex-1 items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none sm:flex-none"
                                        :class="
                                            !isPercentage
                                                ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                : 'border-gray-200 hover:border-gray-300'
                                        "
                                    >
                                        <BadgeDollarSign
                                            class="h-5 w-5 shrink-0"
                                        />
                                        <div>
                                            <p class="text-sm font-semibold">
                                                Fixed Amount
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                e.g. ₱50 off the total
                                            </p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Value -->
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label
                                    class="text-sm font-medium"
                                    for="discount-value"
                                >
                                    {{
                                        isPercentage
                                            ? 'Discount (%)'
                                            : 'Discount Amount (₱)'
                                    }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Input
                                        id="discount-value"
                                        v-model="form.value"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :placeholder="
                                            isPercentage ? 'e.g. 10' : 'e.g. 50'
                                        "
                                        :class="{
                                            'border-red-500': form.errors.value,
                                        }"
                                    />
                                    <span
                                        class="pointer-events-none absolute top-2.5 right-3 text-xs text-muted-foreground"
                                    >
                                        {{ isPercentage ? '%' : '₱' }}
                                    </span>
                                </div>
                                <p
                                    v-if="form.errors.value"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.value }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- ── Validity Period ────────────────────── -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Validity period</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Optional. Limit the promotion to a date range.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label
                                    class="text-sm font-medium"
                                    for="starts-at"
                                    >Start Date</label
                                >
                                <Input
                                    id="starts-at"
                                    v-model="form.starts_at"
                                    type="date"
                                    :class="{
                                        'border-red-500': form.errors.starts_at,
                                    }"
                                />
                                <p
                                    v-if="form.errors.starts_at"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.starts_at }}
                                </p>
                            </div>

                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label class="text-sm font-medium" for="ends-at"
                                    >End Date</label
                                >
                                <Input
                                    id="ends-at"
                                    v-model="form.ends_at"
                                    type="date"
                                    :class="{
                                        'border-red-500': form.errors.ends_at,
                                    }"
                                />
                                <p
                                    v-if="form.errors.ends_at"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.ends_at }}
                                </p>
                            </div>

                            <div
                                class="col-span-12 flex items-end pb-0.5 sm:col-span-6"
                            >
                                <div
                                    class="w-full rounded-lg bg-muted/40 px-4 py-2.5 text-xs text-muted-foreground"
                                >
                                    Leave both dates blank for no date
                                    restriction — the promo stays valid as long
                                    as it is active.
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Side panel: the only card on the page -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <section aria-labelledby="promo-summary">
                        <p
                            id="promo-summary"
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Promotion summary
                        </p>
                        <p
                            class="mt-3 truncate text-base font-bold"
                            :class="form.name ? '' : 'text-muted-foreground/60'"
                        >
                            {{ form.name || 'Promotion name' }}
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold tracking-tight"
                            :class="
                                form.value === ''
                                    ? 'text-muted-foreground/60'
                                    : isPercentage
                                      ? 'text-violet-700'
                                      : 'text-amber-700'
                            "
                        >
                            {{ discountText }}
                        </p>
                        <dl class="mt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">
                                    Minimum order
                                </dt>
                                <dd class="font-medium">
                                    {{
                                        form.min_order_amount
                                            ? peso(form.min_order_amount)
                                            : 'None'
                                    }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">Valid</dt>
                                <dd class="text-right font-medium">
                                    {{ validityText }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">Status</dt>
                                <dd
                                    class="font-medium"
                                    :class="
                                        form.is_active
                                            ? 'text-emerald-600'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ form.is_active ? 'Active' : 'Inactive' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-xs text-muted-foreground"
                            role="status"
                            aria-live="polite"
                        >
                            {{ saveStatus }}
                        </p>
                        <Button
                            type="submit"
                            class="mt-3 w-full"
                            :disabled="!form.isDirty || form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="form.processing"
                            @click="router.visit(base)"
                        >
                            Cancel
                        </Button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
