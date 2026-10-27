<script setup lang="ts">
import ServiceCategoryField from '@/components/shop/ServiceCategoryField.vue';
import ServicePhotoInput from '@/components/shop/ServicePhotoInput.vue';
import ServiceSuppliesField, {
    type SupplyLine,
    type SupplyOption,
} from '@/components/shop/ServiceSuppliesField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { scrollToFirstError } from '@/composables/useScrollToFormError';
import {
    serviceCategories,
    type ServiceCategory,
} from '@/constants/serviceCategories';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Check, Loader2, WashingMachine } from 'lucide-vue-next';
import { computed } from 'vue';

type PricingModel = 'per_kg' | 'per_bundle' | 'per_piece';

interface ShopService {
    id: number;
    category: ServiceCategory | string;
    supplies: SupplyLine[];
    service_name: string;
    description: string | null;
    is_active: boolean;
    pricing_model: PricingModel;
    price_per_piece: string | null;
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    photo_url: string | null;
}

const props = defineProps<{
    service: ShopService;
    pricingModels: string[];
    customCategories: {
        value: string;
        label: string;
        description: string | null;
    }[];
    supplyOptions: SupplyOption[];
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/services' : '/staff/operations/services',
);

const DESCRIPTION_LIMIT = 200;

const form = useForm({
    category: props.service.category,
    service_name: props.service.service_name,
    description: props.service.description ?? '',
    is_active: props.service.is_active,
    pricing_model: props.service.pricing_model,
    price_per_kg: (props.service.price_per_kg ?? '') as string | number,
    bundle_weight_kg: (props.service.bundle_weight_kg ?? '') as string | number,
    bundle_price: (props.service.bundle_price ?? '') as string | number,
    price_per_piece: (props.service.price_per_piece ?? '') as string | number,
    photo: null as File | null,
    remove_photo: false,
    uses_supplies: props.service.supplies.length > 0,
    supplies: props.service.supplies.map((line) => ({ ...line })),
});

const isPerKg = computed(() => form.pricing_model === 'per_kg');
const isPerPiece = computed(() => form.pricing_model === 'per_piece');
const isPerBundle = computed(() => form.pricing_model === 'per_bundle');

function selectModel(model: PricingModel) {
    form.pricing_model = model;

    // Clear the other models' fields.
    if (model !== 'per_kg') form.price_per_kg = '';
    if (model !== 'per_bundle') {
        form.bundle_weight_kg = '';
        form.bundle_price = '';
    }
    if (model !== 'per_piece') form.price_per_piece = '';
}

// ─── Preview + checklist ──────────────────────────────────────────────────────

const categoryLabel = computed(() => {
    const label =
        serviceCategories.find((c) => c.value === form.category)?.label ??
        props.customCategories.find((c) => c.value === form.category)?.label ??
        '';

    return label.split(' (')[0];
});

const peso = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const previewPrice = computed(() => {
    if (isPerPiece.value) {
        return form.price_per_piece !== ''
            ? `${peso(form.price_per_piece)}/pc`
            : '₱ —';
    }

    if (isPerKg.value) {
        return form.price_per_kg !== ''
            ? `${peso(form.price_per_kg)}/kg`
            : '₱ —';
    }

    return form.bundle_price !== '' && form.bundle_weight_kg !== ''
        ? `${peso(form.bundle_price)} / ${form.bundle_weight_kg}kg`
        : '₱ —';
});

const hasPrice = computed(() => {
    if (isPerKg.value) return Number(form.price_per_kg) > 0;
    if (isPerPiece.value) return Number(form.price_per_piece) > 0;

    return Number(form.bundle_price) > 0 && Number(form.bundle_weight_kg) > 0;
});

const checklist = computed(() => [
    { label: 'Category selected', done: form.category !== '' },
    { label: 'Service name', done: form.service_name.trim() !== '' },
    { label: 'Price', done: hasPrice.value },
]);

const ready = computed(() => checklist.value.every((item) => item.done));

const photoPreview = computed(() =>
    form.photo
        ? URL.createObjectURL(form.photo)
        : form.remove_photo
          ? null
          : props.service.photo_url,
);

// Scrolling the page with a number box focused must not nudge its value (200 becoming 199.99).
const blurOnWheel = (event: WheelEvent) =>
    (event.target as HTMLInputElement).blur();

const submit = () => {
    // A file upload cannot ride on a real PUT, so it is sent as a POST that Laravel treats as PUT.
    form.transform((data) => ({ ...data, _method: 'put' })).post(
        `${base.value}/${props.service.id}`,
        { forceFormData: true, onError: scrollToFirstError },
    );
};
</script>

<template>
    <Head title="Edit Service" />

    <ShopLayout title="Edit Service">
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
                            >Service &amp; Pricing</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground"
                            >Edit service</span
                        >
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Edit service
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update the details for "{{ service.service_name }}".
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="router.visit(base)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to services
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Left column -->
                <div class="space-y-8">
                    <!-- Service type -->
                    <section>
                        <h3 class="text-base font-semibold">Service type</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Choose the category this service belongs to.
                        </p>

                        <div class="mt-4">
                            <ServiceCategoryField
                                v-model="form.category"
                                :custom-categories="customCategories"
                                :base="base"
                                :error="form.errors.category"
                            />
                        </div>
                    </section>

                    <!-- Details -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Details</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            What customers see when they book.
                        </p>

                        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div class="space-y-5 sm:col-span-2">
                                <div class="space-y-1.5">
                                    <label
                                        class="text-sm font-medium"
                                        for="service-name"
                                        >Service name
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <Input
                                        id="service-name"
                                        v-model="form.service_name"
                                        placeholder="e.g. Wash & Fold, Dry Cleaning"
                                        :class="{
                                            'border-red-500':
                                                form.errors.service_name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.service_name"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.service_name }}
                                    </p>
                                </div>
                            </div>

                            <ServicePhotoInput
                                v-model="form.photo"
                                v-model:removed="form.remove_photo"
                                :current-url="service.photo_url"
                                compact
                                :error="form.errors.photo"
                            />

                            <div class="space-y-1.5 sm:col-span-3">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="text-sm font-medium"
                                        for="service-description"
                                        >Description
                                        <span
                                            class="font-normal text-muted-foreground"
                                            >(optional)</span
                                        ></label
                                    >
                                    <span
                                        class="text-xs text-muted-foreground tabular-nums"
                                        >{{ form.description.length }} /
                                        {{ DESCRIPTION_LIMIT }}</span
                                    >
                                </div>
                                <Textarea
                                    id="service-description"
                                    v-model="form.description"
                                    :maxlength="DESCRIPTION_LIMIT"
                                    placeholder="Describe what this service includes…"
                                    rows="4"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Pricing -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Pricing</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Choose how this service is charged.
                        </p>

                        <div
                            class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3"
                            role="radiogroup"
                            aria-label="Pricing model"
                        >
                            <button
                                type="button"
                                role="radio"
                                :aria-checked="isPerKg"
                                class="flex items-start gap-3 rounded-lg border px-4 py-3 text-left transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    isPerKg
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                        : 'hover:border-foreground/30'
                                "
                                @click="selectModel('per_kg')"
                            >
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        isPerKg
                                            ? 'border-blue-500'
                                            : 'border-input'
                                    "
                                >
                                    <span
                                        v-if="isPerKg"
                                        class="h-2 w-2 rounded-full bg-blue-500"
                                    />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold"
                                        >Per KG</span
                                    >
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >Charge by actual weight</span
                                    >
                                </span>
                            </button>

                            <button
                                type="button"
                                role="radio"
                                :aria-checked="isPerBundle"
                                class="flex items-start gap-3 rounded-lg border px-4 py-3 text-left transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    isPerBundle
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                        : 'hover:border-foreground/30'
                                "
                                @click="selectModel('per_bundle')"
                            >
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        isPerBundle
                                            ? 'border-blue-500'
                                            : 'border-input'
                                    "
                                >
                                    <span
                                        v-if="isPerBundle"
                                        class="h-2 w-2 rounded-full bg-blue-500"
                                    />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold"
                                        >Per bundle</span
                                    >
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >Fixed price per size</span
                                    >
                                </span>
                            </button>

                            <button
                                type="button"
                                role="radio"
                                :aria-checked="isPerPiece"
                                class="flex items-start gap-3 rounded-lg border px-4 py-3 text-left transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    isPerPiece
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                        : 'hover:border-foreground/30'
                                "
                                @click="selectModel('per_piece')"
                            >
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        isPerPiece
                                            ? 'border-blue-500'
                                            : 'border-input'
                                    "
                                >
                                    <span
                                        v-if="isPerPiece"
                                        class="h-2 w-2 rounded-full bg-blue-500"
                                    />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold"
                                        >Per piece</span
                                    >
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >Comforters, shoes, bags</span
                                    >
                                </span>
                            </button>
                        </div>

                        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div v-if="isPerPiece" class="space-y-1.5">
                                <label
                                    class="text-sm font-medium"
                                    for="price-per-piece"
                                    >Price per piece
                                    <span class="text-red-500">*</span></label
                                >
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute top-2.5 left-3 text-sm text-muted-foreground"
                                        >₱</span
                                    >
                                    <Input
                                        id="price-per-piece"
                                        v-model="form.price_per_piece"
                                        type="number"
                                        @wheel="blurOnWheel"
                                        min="0"
                                        step="0.01"
                                        placeholder="250.00"
                                        class="pl-7"
                                        :class="{
                                            'border-red-500':
                                                form.errors.price_per_piece,
                                        }"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.price_per_piece"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.price_per_piece }}
                                </p>
                            </div>

                            <div v-else-if="isPerKg" class="space-y-1.5">
                                <label
                                    class="text-sm font-medium"
                                    for="price-per-kg"
                                    >Price per KG
                                    <span class="text-red-500">*</span></label
                                >
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute top-2.5 left-3 text-sm text-muted-foreground"
                                        >₱</span
                                    >
                                    <Input
                                        id="price-per-kg"
                                        v-model="form.price_per_kg"
                                        type="number"
                                        @wheel="blurOnWheel"
                                        min="0"
                                        step="0.01"
                                        placeholder="55.00"
                                        class="pl-7"
                                        :class="{
                                            'border-red-500':
                                                form.errors.price_per_kg,
                                        }"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.price_per_kg"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.price_per_kg }}
                                </p>
                            </div>

                            <template v-else>
                                <div class="space-y-1.5">
                                    <label
                                        class="text-sm font-medium"
                                        for="bundle-weight"
                                        >Bundle weight
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div class="relative">
                                        <Input
                                            id="bundle-weight"
                                            v-model="form.bundle_weight_kg"
                                            type="number"
                                            @wheel="blurOnWheel"
                                            min="0.1"
                                            step="0.1"
                                            placeholder="7"
                                            :class="{
                                                'border-red-500':
                                                    form.errors
                                                        .bundle_weight_kg,
                                            }"
                                        />
                                        <span
                                            class="pointer-events-none absolute top-2.5 right-3 text-xs text-muted-foreground"
                                            >kg</span
                                        >
                                    </div>
                                    <p
                                        v-if="form.errors.bundle_weight_kg"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.bundle_weight_kg }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <label
                                        class="text-sm font-medium"
                                        for="bundle-price"
                                        >Bundle price
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute top-2.5 left-3 text-sm text-muted-foreground"
                                            >₱</span
                                        >
                                        <Input
                                            id="bundle-price"
                                            v-model="form.bundle_price"
                                            type="number"
                                            @wheel="blurOnWheel"
                                            min="0"
                                            step="0.01"
                                            placeholder="180.00"
                                            class="pl-7"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.bundle_price,
                                            }"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.bundle_price"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.bundle_price }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </section>

                    <!-- Supplies -->
                    <section class="border-t pt-8">
                        <ServiceSuppliesField
                            v-model:uses-supplies="form.uses_supplies"
                            v-model:supplies="form.supplies"
                            :options="supplyOptions"
                            :per-label="
                                isPerKg ? 'kg' : isPerPiece ? 'piece' : 'bundle'
                            "
                            :errors="form.errors"
                        />
                    </section>
                </div>

                <!-- Right column -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <section>
                        <div class="flex items-center justify-between">
                            <p
                                class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Customer preview
                            </p>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-medium"
                                    :class="
                                        form.is_active
                                            ? 'text-blue-600'
                                            : 'text-muted-foreground'
                                    "
                                    >{{
                                        form.is_active ? 'Active' : 'Inactive'
                                    }}</span
                                >
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="form.is_active"
                                    aria-label="Service is active"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="
                                        form.is_active
                                            ? 'bg-blue-500'
                                            : 'bg-gray-300'
                                    "
                                    @click="form.is_active = !form.is_active"
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
                            </div>
                        </div>

                        <div class="mt-3 overflow-hidden rounded-xl border">
                            <div
                                class="flex h-24 items-center justify-center bg-blue-50 text-blue-500 dark:bg-blue-950/40"
                            >
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                                <WashingMachine v-else class="h-8 w-8" />
                            </div>
                            <div class="space-y-1 p-3">
                                <p class="text-xs font-semibold text-blue-600">
                                    {{ categoryLabel }}
                                </p>
                                <p
                                    class="truncate text-base font-bold"
                                    :class="
                                        form.service_name
                                            ? ''
                                            : 'text-muted-foreground/60'
                                    "
                                >
                                    {{ form.service_name || 'Service name' }}
                                </p>
                                <div
                                    class="flex items-center justify-between border-t pt-2 text-xs"
                                >
                                    <span class="font-bold">{{
                                        previewPrice
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Ready to save
                        </p>
                        <ul class="mt-3 space-y-2">
                            <li
                                v-for="item in checklist"
                                :key="item.label"
                                class="flex items-center gap-2.5 text-sm"
                            >
                                <span
                                    class="flex h-4 w-4 items-center justify-center rounded-full border"
                                    :class="
                                        item.done
                                            ? 'border-blue-500 bg-blue-500 text-white'
                                            : 'border-input text-muted-foreground'
                                    "
                                >
                                    <Check class="h-3 w-3" />
                                </span>
                                <span
                                    :class="
                                        item.done ? '' : 'text-muted-foreground'
                                    "
                                    >{{ item.label }}</span
                                >
                                <span v-if="!item.done" class="sr-only"
                                    >(missing)</span
                                >
                            </li>
                        </ul>

                        <Button
                            type="submit"
                            class="mt-4 w-full"
                            :disabled="!ready || form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save changes' }}
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
