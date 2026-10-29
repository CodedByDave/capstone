<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Check,
    ChevronLeft,
    ChevronRight,
    MapPin,
    PackageCheck,
    Phone,
    Store,
    Truck,
} from 'lucide-vue-next';
import { ref } from 'vue';

type OwnershipType = 'independent' | 'company_owned' | 'franchisee';

const props = defineProps<{
    shop: {
        shop_name: string;
        phone: string;
        address: string;
        ownership_type: OwnershipType;
        franchise_brand_name: string;
        franchisor_name: string;
        offers_pickup: boolean;
        offers_delivery: boolean;
        setup_completed: boolean;
    };
    plan: {
        name: string;
        is_trial: boolean;
    };
}>();

const steps = [
    { title: 'Confirm shop', caption: 'Registered location' },
    { title: 'Fulfillment', caption: 'Pickup and delivery' },
    { title: 'Review', caption: 'Save your setup' },
];
const step = ref(1);
const totalSteps = 3;
const form = useForm({
    ownership_type: props.shop.ownership_type,
    franchise_brand_name: props.shop.franchise_brand_name,
    franchisor_name: props.shop.franchisor_name,
    offers_pickup: props.shop.offers_pickup,
    offers_delivery: props.shop.offers_delivery,
});

function next() {
    if (step.value < totalSteps) step.value++;
}

function back() {
    if (step.value > 1) step.value--;
}

function errorFor(key: string): string | undefined {
    return (form.errors as Record<string, string>)[key];
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(
        '/shop/setup',
        {
            forceFormData: true,
        },
    );
}
</script>

<template>
    <Head title="Operations Setup" />

    <ShopLayout title="Operations Setup">
        <main class="min-h-full px-4 py-8 sm:px-8 sm:py-14">
            <div
                class="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] lg:gap-20"
            >
                <aside>
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border bg-primary/10 px-3 py-1 text-xs font-medium text-primary"
                    >
                        <PackageCheck class="h-3.5 w-3.5" />
                        {{ plan.name }} plan
                    </div>
                    <h1
                        class="text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Set up<br />your shop
                    </h1>
                    <p class="mt-4 max-w-sm text-sm text-muted-foreground">
                        Three quick steps to configure pickup and delivery. You
                        can change them later in Shop Settings.
                    </p>

                    <ol class="mt-8" aria-label="Setup progress">
                        <li
                            v-for="(item, index) in steps"
                            :key="item.title"
                            class="relative flex gap-4 pb-6 last:pb-0"
                            :aria-current="
                                step === index + 1 ? 'step' : undefined
                            "
                        >
                            <span
                                v-if="index < steps.length - 1"
                                class="absolute top-8 bottom-0 left-4 -ml-px w-px bg-border"
                                aria-hidden="true"
                            />
                            <span
                                class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-xs font-medium"
                                :class="
                                    step === index + 1
                                        ? 'border-primary bg-primary/10 text-primary ring-4 ring-primary/20'
                                        : step > index + 1
                                          ? 'border-primary bg-primary text-primary-foreground'
                                          : 'bg-background text-muted-foreground'
                                "
                            >
                                <Check
                                    v-if="step > index + 1"
                                    class="h-4 w-4"
                                />
                                <template v-else>{{ index + 1 }}</template>
                            </span>
                            <span>
                                <span
                                    class="block text-sm font-medium"
                                    :class="
                                        step === index + 1
                                            ? 'text-foreground'
                                            : 'text-muted-foreground'
                                    "
                                    >{{ item.title }}</span
                                >
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >{{ item.caption }}</span
                                >
                            </span>
                        </li>
                    </ol>
                </aside>

                <section>
                    <p
                        class="text-[11px] font-medium tracking-widest text-muted-foreground uppercase"
                    >
                        Step {{ step }} of {{ totalSteps }}
                    </p>

                    <div v-if="step === 1">
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                            Confirm your registered shop
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            This is the main location registered to your owner
                            account.
                        </p>
                        <dl class="mt-8 divide-y border-y">
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Store class="h-4 w-4" />Shop name
                                </dt>
                                <dd class="text-right text-sm font-semibold">
                                    {{ shop.shop_name }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Phone class="h-4 w-4" />Phone
                                </dt>
                                <dd class="text-right text-sm font-semibold">
                                    {{ shop.phone }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <MapPin class="h-4 w-4" />Address
                                </dt>
                                <dd class="text-right text-sm font-semibold">
                                    {{
                                        shop.address || 'No address recorded'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div v-else-if="step === 2">
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                            Choose customer fulfillment options
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Walk-in service remains available. Enable only the
                            extra services your shop provides.
                        </p>
                        <div class="mt-8 divide-y border-y">
                            <div class="flex items-center gap-4 py-4">
                                <Store class="h-5 w-5 text-primary" />
                                <div class="flex-1">
                                    <p class="text-sm font-semibold">
                                        Customers visit the shop
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Available by default.
                                    </p>
                                </div>
                                <Check class="h-5 w-5 text-emerald-600" />
                            </div>
                            <label
                                class="flex cursor-pointer items-center gap-4 py-4"
                            >
                                <PackageCheck class="h-5 w-5 text-primary" />
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold"
                                        >Pickup from customer</span
                                    >
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >Customers may ask the shop to collect
                                        their laundry.</span
                                    >
                                </span>
                                <input
                                    v-model="form.offers_pickup"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-border accent-primary"
                                />
                            </label>
                            <label
                                class="flex cursor-pointer items-center gap-4 py-4"
                            >
                                <Truck class="h-5 w-5 text-primary" />
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold"
                                        >Delivery to customer</span
                                    >
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >Enable riders, logistics, and delivery
                                        requests.</span
                                    >
                                </span>
                                <input
                                    v-model="form.offers_delivery"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-border accent-primary"
                                />
                            </label>
                        </div>
                    </div>

                    <div v-else>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                            Review your setup
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Check your answers before saving.
                        </p>
                        <InputError class="mt-4" :message="errorFor('setup')" />
                        <dl class="mt-8 divide-y border-y">
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt class="text-sm text-muted-foreground">
                                    Subscription
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ plan.name }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt class="text-sm text-muted-foreground">
                                    Customer pickup
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{
                                        form.offers_pickup
                                            ? 'Enabled'
                                            : 'Disabled'
                                    }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-6 py-4"
                            >
                                <dt class="text-sm text-muted-foreground">
                                    Customer delivery
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{
                                        form.offers_delivery
                                            ? 'Enabled'
                                            : 'Disabled'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <Button
                            v-if="step > 1"
                            type="button"
                            variant="outline"
                            @click="back"
                            ><ChevronLeft class="mr-2 h-4 w-4" />
                            Back</Button
                        >
                        <Button
                            v-if="step < totalSteps"
                            type="button"
                            @click="next"
                            >Continue <ChevronRight class="ml-2 h-4 w-4"
                        /></Button>
                        <Button
                            v-else
                            type="button"
                            :disabled="form.processing"
                            @click="submit"
                            ><Check class="mr-2 h-4 w-4" />{{
                                form.processing
                                    ? 'Saving...'
                                    : shop.setup_completed
                                      ? 'Save Changes'
                                      : 'Finish Setup'
                            }}</Button
                        >
                    </div>
                </section>
            </div>
        </main>
    </ShopLayout>
</template>
