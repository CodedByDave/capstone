<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, BadgeDollarSign, Loader2, Percent } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/promos' : '/staff/operations/promos',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Operations', href: '/shop/operations/orders' },
    { title: 'Promotions', href: '/shop/operations/promos' },
    { title: 'New Promotion', href: '/shop/operations/promos/create' },
];

const form = useForm({
    name: '',
    description: '',
    type: 'percentage' as 'percentage' | 'fixed',
    value: '' as string | number,
    min_order_amount: '' as string | number,
    is_active: true,
    starts_at: '',
    ends_at: '',
});

const isPercentage = computed(() => form.type === 'percentage');

const onTypeChange = () => {
    form.value = '';
};

const submit = () => form.post(base.value);
</script>

<template>
    <Head title="New Promotion" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="New Promotion">
        <div class="space-y-8 px-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Promotion</h2>
                    <p class="text-sm text-muted-foreground">
                        Create a discount promotion for customer orders.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- ── Basic Information ──────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Basic Information
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 space-y-1 sm:col-span-5">
                            <label class="text-sm font-medium"
                                >Promotion Name
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="form.name"
                                placeholder="e.g. Summer Sale, Senior Discount"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="col-span-12 space-y-1 sm:col-span-4">
                            <label class="text-sm font-medium"
                                >Minimum Order Amount (₱)</label
                            >
                            <Input
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
                            <label class="text-sm font-medium">Status</label>
                            <div class="flex h-10 items-center gap-3">
                                <button
                                    type="button"
                                    @click="form.is_active = !form.is_active"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
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
                                    {{ form.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-span-12 space-y-1">
                            <label class="text-sm font-medium"
                                >Description
                                <span class="text-xs text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <Textarea
                                v-model="form.description"
                                placeholder="Describe what this promotion is for…"
                                rows="2"
                            />
                        </div>
                    </div>
                </div>

                <!-- ── Discount Type ──────────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Discount
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <!-- Type Selector -->
                        <div class="col-span-12 space-y-2">
                            <label class="text-sm font-medium"
                                >Discount Type
                                <span class="text-red-500">*</span></label
                            >
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="
                                        form.type = 'percentage';
                                        onTypeChange();
                                    "
                                    class="flex flex-1 items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition sm:flex-none"
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
                                    @click="
                                        form.type = 'fixed';
                                        onTypeChange();
                                    "
                                    class="flex flex-1 items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition sm:flex-none"
                                    :class="
                                        !isPercentage
                                            ? 'border-amber-500 bg-amber-50 text-amber-700'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                >
                                    <BadgeDollarSign class="h-5 w-5 shrink-0" />
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
                            <label class="text-sm font-medium">
                                {{
                                    isPercentage
                                        ? 'Discount (%)'
                                        : 'Discount Amount (₱)'
                                }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <Input
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
                                    class="absolute top-2.5 right-3 text-xs text-muted-foreground"
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

                        <!-- Preview -->
                        <div
                            v-if="form.value"
                            class="col-span-12 space-y-1 sm:col-span-4"
                        >
                            <label class="text-sm font-medium">Preview</label>
                            <div
                                class="flex h-10 items-center rounded-lg border px-3"
                                :class="
                                    isPercentage
                                        ? 'border-violet-200 bg-violet-50'
                                        : 'border-amber-200 bg-amber-50'
                                "
                            >
                                <span
                                    class="text-sm font-semibold"
                                    :class="
                                        isPercentage
                                            ? 'text-violet-700'
                                            : 'text-amber-700'
                                    "
                                >
                                    {{
                                        isPercentage
                                            ? `${form.value}% off`
                                            : `₱${Number(form.value).toLocaleString('en-PH', { minimumFractionDigits: 2 })} off`
                                    }}
                                    <span
                                        v-if="form.min_order_amount"
                                        class="ml-1 text-xs font-normal"
                                    >
                                        (min ₱{{
                                            Number(
                                                form.min_order_amount,
                                            ).toLocaleString('en-PH')
                                        }})
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Validity Period ────────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Validity Period
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Start Date</label
                            >
                            <Input
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
                            <label class="text-sm font-medium">End Date</label>
                            <Input
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
                                class="w-full rounded-lg border bg-muted/40 px-4 py-2.5 text-xs text-muted-foreground"
                            >
                                Leave both dates blank for no date restriction —
                                the promo stays valid as long as it is active.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 border-t pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="router.visit(base)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ form.processing ? 'Creating…' : 'Create Promotion' }}
                    </Button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>
