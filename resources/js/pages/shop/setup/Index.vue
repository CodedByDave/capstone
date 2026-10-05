<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

const props = defineProps<{
    shop: {
        shop_name: string;
        phone: string;
        address: string;
        location_mode: 'single' | 'multiple';
        offers_pickup: boolean;
        offers_delivery: boolean;
        setup_completed: boolean;
    };
    plan: {
        name: string;
        is_trial: boolean;
        allows_multiple_locations: boolean;
    };
}>();

const step = ref(1);
const totalSteps = 4;
const form = useForm({
    location_mode: props.shop.location_mode,
    offers_pickup: props.shop.offers_pickup,
    offers_delivery: props.shop.offers_delivery,
});

const locationLabel = computed(() =>
    form.location_mode === 'multiple' ? 'Multiple locations' : 'One location',
);

function chooseLocation(mode: 'single' | 'multiple') {
    if (mode === 'multiple' && !props.plan.allows_multiple_locations) return;
    form.location_mode = mode;
}

function next() {
    if (step.value < totalSteps) step.value++;
}

function back() {
    if (step.value > 1) step.value--;
}

function submit() {
    form.put('/shop/setup');
}
</script>

<template>
    <Head title="Set Up Your Shop" />

    <main class="min-h-screen bg-muted/30 px-4 py-8 sm:py-12">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8 text-center">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border bg-background px-3 py-1 text-xs font-medium">
                    <PackageCheck class="h-3.5 w-3.5 text-primary" />
                    {{ plan.name }} plan
                </div>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Set up your shop</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Tell us how your business operates. You can change these settings later.
                </p>
            </div>

            <div class="mb-6 grid grid-cols-4 gap-2" aria-label="Setup progress">
                <div v-for="item in totalSteps" :key="item" class="space-y-2">
                    <div
                        class="h-1.5 rounded-full transition-colors"
                        :class="item <= step ? 'bg-primary' : 'bg-border'"
                    />
                    <p class="text-center text-[11px] text-muted-foreground">Step {{ item }}</p>
                </div>
            </div>

            <Card>
                <CardContent class="p-6 sm:p-8">
                    <section v-if="step === 1" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Confirm your main shop</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                This registered address is your main location. It will not be created as a branch automatically.
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/30 p-5">
                            <div class="flex items-start gap-3">
                                <Store class="mt-0.5 h-5 w-5 text-primary" />
                                <div class="min-w-0 space-y-2">
                                    <p class="font-semibold">{{ shop.shop_name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ shop.phone }}</p>
                                    <p class="flex items-start gap-2 text-sm text-muted-foreground">
                                        <MapPin class="mt-0.5 h-4 w-4 shrink-0" />
                                        {{ shop.address || 'No address recorded' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-else-if="step === 2" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">How many locations do you operate?</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Single-location shops will not see unnecessary branch fields or navigation.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                class="rounded-xl border p-5 text-left transition"
                                :class="form.location_mode === 'single' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'hover:border-primary/40'"
                                @click="chooseLocation('single')"
                            >
                                <Store class="mb-3 h-6 w-6 text-primary" />
                                <p class="font-semibold">One location</p>
                                <p class="mt-1 text-xs text-muted-foreground">Use the registered shop as your only operating location.</p>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border p-5 text-left transition disabled:cursor-not-allowed disabled:opacity-60"
                                :class="form.location_mode === 'multiple' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'hover:border-primary/40'"
                                :disabled="!plan.allows_multiple_locations"
                                @click="chooseLocation('multiple')"
                            >
                                <Building2 class="mb-3 h-6 w-6 text-primary" />
                                <p class="font-semibold">Multiple locations</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ plan.allows_multiple_locations ? 'Enable Branch Management and add locations when ready.' : 'Available with the Premium plan.' }}
                                </p>
                            </button>
                        </div>
                        <InputError :message="form.errors.location_mode" />
                    </section>

                    <section v-else-if="step === 3" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Choose customer fulfillment options</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Walk-in service remains available. Enable only the extra services your shop actually provides.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-4 rounded-xl border bg-muted/20 p-4">
                                <Store class="h-5 w-5 text-primary" />
                                <div class="flex-1">
                                    <p class="text-sm font-semibold">Customers visit the shop</p>
                                    <p class="text-xs text-muted-foreground">Available by default for walk-in and drop-off orders.</p>
                                </div>
                                <Check class="h-5 w-5 text-emerald-600" />
                            </div>

                            <label class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40">
                                <PackageCheck class="h-5 w-5 text-primary" />
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold">Pickup from customer</span>
                                    <span class="block text-xs text-muted-foreground">Customers may ask your shop to collect their laundry.</span>
                                </span>
                                <input v-model="form.offers_pickup" type="checkbox" class="h-5 w-5 rounded border-border accent-primary" />
                            </label>

                            <label class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40">
                                <Truck class="h-5 w-5 text-primary" />
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold">Delivery to customer</span>
                                    <span class="block text-xs text-muted-foreground">Enable riders, logistics, and completed-order delivery requests.</span>
                                </span>
                                <input v-model="form.offers_delivery" type="checkbox" class="h-5 w-5 rounded border-border accent-primary" />
                            </label>
                        </div>
                    </section>

                    <section v-else class="space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold">Review your setup</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Nothing is created automatically. Only the workflows below will be enabled.</p>
                        </div>

                        <dl class="divide-y rounded-xl border">
                            <div class="flex items-center justify-between gap-4 p-4">
                                <dt class="text-sm text-muted-foreground">Subscription</dt>
                                <dd class="text-sm font-semibold">{{ plan.name }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 p-4">
                                <dt class="text-sm text-muted-foreground">Locations</dt>
                                <dd class="text-sm font-semibold">{{ locationLabel }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 p-4">
                                <dt class="text-sm text-muted-foreground">Customer pickup</dt>
                                <dd class="text-sm font-semibold">{{ form.offers_pickup ? 'Enabled' : 'Disabled' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 p-4">
                                <dt class="text-sm text-muted-foreground">Customer delivery</dt>
                                <dd class="text-sm font-semibold">{{ form.offers_delivery ? 'Enabled' : 'Disabled' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <div class="mt-8 flex items-center justify-between border-t pt-5">
                        <Button v-if="step > 1" type="button" variant="outline" @click="back">
                            <ChevronLeft class="mr-2 h-4 w-4" /> Back
                        </Button>
                        <span v-else />

                        <Button v-if="step < totalSteps" type="button" @click="next">
                            Continue <ChevronRight class="ml-2 h-4 w-4" />
                        </Button>
                        <Button v-else type="button" :disabled="form.processing" @click="submit">
                            <Check class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Saving...' : props.shop.setup_completed ? 'Save Changes' : 'Finish Setup' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </main>
</template>
