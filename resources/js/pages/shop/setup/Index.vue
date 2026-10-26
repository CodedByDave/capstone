<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Building2,
    Check,
    ChevronLeft,
    ChevronRight,
    MapPin,
    PackageCheck,
    Store,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

const step = ref(1);
const totalSteps = 4;
const localErrors = ref<Record<string, string>>({});
const form = useForm({
    ownership_type: props.shop.ownership_type,
    franchise_brand_name: props.shop.franchise_brand_name,
    franchisor_name: props.shop.franchisor_name,
    offers_pickup: props.shop.offers_pickup,
    offers_delivery: props.shop.offers_delivery,
});

const ownershipLabel = computed(
    () =>
        ({
            independent: 'Independent business',
            company_owned: 'Company-owned business',
            franchisee: 'Franchise operator',
        })[form.ownership_type],
);

function chooseOwnership(type: OwnershipType) {
    form.ownership_type = type;
    if (type !== 'franchisee') {
        form.franchise_brand_name = '';
        form.franchisor_name = '';
    }
}

function validateCurrentStep(): boolean {
    const errors: Record<string, string> = {};

    if (step.value === 2 && form.ownership_type === 'franchisee') {
        if (!form.franchise_brand_name.trim())
            errors.franchise_brand_name = 'Enter the franchise brand name.';
        if (!form.franchisor_name.trim())
            errors.franchisor_name =
                'Enter the franchisor or franchise company.';
    }

    localErrors.value = errors;
    return Object.keys(errors).length === 0;
}

function next() {
    if (validateCurrentStep() && step.value < totalSteps) step.value++;
}

function back() {
    localErrors.value = {};
    if (step.value > 1) step.value--;
}

function errorFor(key: string): string | undefined {
    return (
        localErrors.value[key] || (form.errors as Record<string, string>)[key]
    );
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
        <main class="min-h-full bg-muted/30 px-4 py-8 sm:py-12">
            <div class="mx-auto max-w-4xl">
                <div class="mb-8 text-center">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border bg-background px-3 py-1 text-xs font-medium"
                    >
                        <PackageCheck class="h-3.5 w-3.5 text-primary" />
                        {{ plan.name }} plan
                    </div>
                    <h1
                        class="text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        Set up your shop
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Configure ownership, pickup, and delivery.
                    </p>
                </div>

                <div
                    class="mb-6 grid grid-cols-4 gap-2"
                    aria-label="Setup progress"
                >
                    <div
                        v-for="item in totalSteps"
                        :key="item"
                        class="space-y-2"
                    >
                        <div
                            class="h-1.5 rounded-full transition-colors"
                            :class="item <= step ? 'bg-primary' : 'bg-border'"
                        />
                        <p
                            class="text-center text-[11px] text-muted-foreground"
                        >
                            Step {{ item }}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardContent class="p-6 sm:p-8">
                        <section v-if="step === 1" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Confirm your registered shop
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    This is the main location registered to this
                                    owner account.
                                </p>
                            </div>
                            <div class="rounded-xl border bg-muted/30 p-5">
                                <div class="flex items-start gap-3">
                                    <Store
                                        class="mt-0.5 h-5 w-5 text-primary"
                                    />
                                    <div class="min-w-0 space-y-2">
                                        <p class="font-semibold">
                                            {{ shop.shop_name }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ shop.phone }}
                                        </p>
                                        <p
                                            class="flex items-start gap-2 text-sm text-muted-foreground"
                                        >
                                            <MapPin
                                                class="mt-0.5 h-4 w-4 shrink-0"
                                            />
                                            {{
                                                shop.address ||
                                                'No address recorded'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section v-else-if="step === 2" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    How is this shop owned?
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    A shared shop name or branding does not mean
                                    the shops have the same owner.
                                </p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <button
                                    v-for="option in [
                                        {
                                            value: 'independent',
                                            label: 'Independent',
                                            text: 'A standalone business operated by this owner.',
                                        },
                                        {
                                            value: 'company_owned',
                                            label: 'Company-owned',
                                            text: 'The same company owns and operates this shop.',
                                        },
                                        {
                                            value: 'franchisee',
                                            label: 'Franchise operator',
                                            text: 'This owner operates the shop under a franchise brand.',
                                        },
                                    ]"
                                    :key="option.value"
                                    type="button"
                                    class="rounded-xl border p-5 text-left transition"
                                    :class="
                                        form.ownership_type === option.value
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/40'
                                    "
                                    @click="
                                        chooseOwnership(
                                            option.value as OwnershipType,
                                        )
                                    "
                                >
                                    <Building2
                                        class="mb-3 h-6 w-6 text-primary"
                                    />
                                    <p class="font-semibold">
                                        {{ option.label }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ option.text }}
                                    </p>
                                </button>
                            </div>
                            <div
                                v-if="form.ownership_type === 'franchisee'"
                                class="space-y-4 rounded-xl border bg-muted/20 p-5"
                            >
                                <div>
                                    <Label class="mb-2"
                                        >Franchise Brand Name</Label
                                    >
                                    <Input
                                        v-model="form.franchise_brand_name"
                                        placeholder="Brand shown to customers"
                                    />
                                    <InputError
                                        :message="
                                            errorFor('franchise_brand_name')
                                        "
                                    />
                                </div>
                                <div>
                                    <Label class="mb-2"
                                        >Franchisor / Franchise Company</Label
                                    >
                                    <Input
                                        v-model="form.franchisor_name"
                                        placeholder="Legal franchisor name"
                                    />
                                    <InputError
                                        :message="errorFor('franchisor_name')"
                                    />
                                </div>
                            </div>
                        </section>

                        <section v-else-if="step === 3" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Choose customer fulfillment options
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Walk-in service remains available. Enable
                                    only the extra services your shop provides.
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div
                                    class="flex items-center gap-4 rounded-xl border bg-muted/20 p-4"
                                >
                                    <Store class="h-5 w-5 text-primary" />
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">
                                            Customers visit the shop
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Available by default.
                                        </p>
                                    </div>
                                    <Check class="h-5 w-5 text-emerald-600" />
                                </div>
                                <label
                                    class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40"
                                >
                                    <PackageCheck
                                        class="h-5 w-5 text-primary"
                                    />
                                    <span class="flex-1"
                                        ><span
                                            class="block text-sm font-semibold"
                                            >Pickup from customer</span
                                        ><span
                                            class="block text-xs text-muted-foreground"
                                            >Customers may ask the shop to
                                            collect their laundry.</span
                                        ></span
                                    >
                                    <input
                                        v-model="form.offers_pickup"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-border accent-primary"
                                    />
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40"
                                >
                                    <Truck class="h-5 w-5 text-primary" />
                                    <span class="flex-1"
                                        ><span
                                            class="block text-sm font-semibold"
                                            >Delivery to customer</span
                                        ><span
                                            class="block text-xs text-muted-foreground"
                                            >Enable riders, logistics, and
                                            delivery requests.</span
                                        ></span
                                    >
                                    <input
                                        v-model="form.offers_delivery"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-border accent-primary"
                                    />
                                </label>
                            </div>
                        </section>

                        <section v-else class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Review your setup
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Check your answers before saving.
                                </p>
                            </div>
                            <InputError :message="errorFor('setup')" />
                            <dl class="divide-y rounded-xl border">
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Subscription
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{ plan.name }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Ownership
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{ ownershipLabel }}
                                    </dd>
                                </div>
                                <div
                                    v-if="form.ownership_type === 'franchisee'"
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Franchise
                                    </dt>
                                    <dd
                                        class="text-right text-sm font-semibold"
                                    >
                                        {{ form.franchise_brand_name
                                        }}<span
                                            class="block text-xs font-normal text-muted-foreground"
                                            >{{ form.franchisor_name }}</span
                                        >
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
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
                                    class="flex items-center justify-between gap-4 p-4"
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
                        </section>

                        <div
                            class="mt-8 flex items-center justify-between border-t pt-5"
                        >
                            <Button
                                v-if="step > 1"
                                type="button"
                                variant="outline"
                                @click="back"
                                ><ChevronLeft class="mr-2 h-4 w-4" />
                                Back</Button
                            ><span v-else />
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
                    </CardContent>
                </Card>
            </div>
        </main>
    </ShopLayout>
</template>
