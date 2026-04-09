<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Loader2, Weight, Package } from 'lucide-vue-next'

defineProps<{ pricingModels: string[] }>()

const base = '/shop/operations/services'

const form = useForm({
    service_name:     '',
    description:      '',
    is_active:        true,
    pricing_model:    'per_kg' as 'per_kg' | 'per_bundle',
    price_per_kg:     '' as string | number,
    bundle_weight_kg: '' as string | number,
    bundle_price:     '' as string | number,
    estimated_hours:  '' as string | number,
})

const isPerKg = computed(() => form.pricing_model === 'per_kg')

const onModelChange = () => {
    // clear opposite model's fields
    if (form.pricing_model === 'per_kg') {
        form.bundle_weight_kg = ''
        form.bundle_price = ''
    } else {
        form.price_per_kg = ''
    }
}

const submit = () => form.post(base)
</script>

<template>
    <Head title="New Service" />

    <ShopLayout title="New Service">
        <div class="px-6 space-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Service</h2>
                    <p class="text-sm text-muted-foreground">Define a service and its pricing model.</p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8">

                <!-- Basic Info -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Basic Information</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">

                        <div class="col-span-12 sm:col-span-5 space-y-1">
                            <label class="text-sm font-medium">Service Name <span class="text-red-500">*</span></label>
                            <Input
                                v-model="form.service_name"
                                placeholder="e.g. Wash & Fold, Dry Cleaning"
                                :class="{ 'border-red-500': form.errors.service_name }"
                            />
                            <p v-if="form.errors.service_name" class="text-xs text-red-500">{{ form.errors.service_name }}</p>
                        </div>

                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Estimated Turnaround</label>
                            <div class="relative">
                                <Input
                                    v-model="form.estimated_hours"
                                    type="number" min="1"
                                    placeholder="e.g. 24"
                                    :class="{ 'border-red-500': form.errors.estimated_hours }"
                                />
                                <span class="absolute right-3 top-2.5 text-xs text-muted-foreground">hours</span>
                            </div>
                            <p v-if="form.errors.estimated_hours" class="text-xs text-red-500">{{ form.errors.estimated_hours }}</p>
                        </div>

                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Status</label>
                            <div class="flex items-center gap-3 h-10">
                                <button
                                    type="button"
                                    @click="form.is_active = !form.is_active"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="form.is_active ? 'bg-emerald-500' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                                        :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                    />
                                </button>
                                <span class="text-sm" :class="form.is_active ? 'text-emerald-600 font-medium' : 'text-muted-foreground'">
                                    {{ form.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-span-12 space-y-1">
                            <label class="text-sm font-medium">Description <span class="text-muted-foreground text-xs">(optional)</span></label>
                            <Textarea v-model="form.description" placeholder="Describe what this service includes…" rows="2" />
                        </div>

                    </div>
                </div>

                <!-- Pricing Model -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Pricing</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">

                        <!-- Model Selector -->
                        <div class="col-span-12 space-y-2">
                            <label class="text-sm font-medium">Pricing Model <span class="text-red-500">*</span></label>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="form.pricing_model = 'per_kg'; onModelChange()"
                                    class="flex-1 sm:flex-none flex items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition"
                                    :class="isPerKg
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <Weight class="h-5 w-5 shrink-0" />
                                    <div>
                                        <p class="font-semibold text-sm">Per KG</p>
                                        <p class="text-xs text-muted-foreground">Charge based on actual weight</p>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    @click="form.pricing_model = 'per_bundle'; onModelChange()"
                                    class="flex-1 sm:flex-none flex items-center gap-3 rounded-xl border-2 px-5 py-4 text-left transition"
                                    :class="!isPerKg
                                        ? 'border-violet-500 bg-violet-50 text-violet-700'
                                        : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <Package class="h-5 w-5 shrink-0" />
                                    <div>
                                        <p class="font-semibold text-sm">Per Bundle</p>
                                        <p class="text-xs text-muted-foreground">Fixed price per bundle size</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Per KG field -->
                        <template v-if="isPerKg">
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Price per KG (₱) <span class="text-red-500">*</span></label>
                                <Input
                                    v-model="form.price_per_kg"
                                    type="number" min="0" step="0.01"
                                    placeholder="e.g. 55.00"
                                    :class="{ 'border-red-500': form.errors.price_per_kg }"
                                />
                                <p v-if="form.errors.price_per_kg" class="text-xs text-red-500">{{ form.errors.price_per_kg }}</p>
                            </div>
                        </template>

                        <!-- Per Bundle fields -->
                        <template v-else>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Weight (kg) <span class="text-red-500">*</span></label>
                                <Input
                                    v-model="form.bundle_weight_kg"
                                    type="number" min="0.1" step="0.1"
                                    placeholder="e.g. 7"
                                    :class="{ 'border-red-500': form.errors.bundle_weight_kg }"
                                />
                                <p class="text-xs text-muted-foreground mt-0.5">Max kg included per bundle</p>
                                <p v-if="form.errors.bundle_weight_kg" class="text-xs text-red-500">{{ form.errors.bundle_weight_kg }}</p>
                            </div>

                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Price (₱) <span class="text-red-500">*</span></label>
                                <Input
                                    v-model="form.bundle_price"
                                    type="number" min="0" step="0.01"
                                    placeholder="e.g. 180.00"
                                    :class="{ 'border-red-500': form.errors.bundle_price }"
                                />
                                <p v-if="form.errors.bundle_price" class="text-xs text-red-500">{{ form.errors.bundle_price }}</p>
                            </div>

                            <!-- Preview -->
                            <div v-if="form.bundle_weight_kg && form.bundle_price" class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Preview</label>
                                <div class="flex items-center h-10 rounded-lg bg-violet-50 border border-violet-200 px-3">
                                    <span class="text-sm font-semibold text-violet-700">
                                        ₱{{ Number(form.bundle_price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                                        / {{ form.bundle_weight_kg }}kg bundle
                                    </span>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Button variant="outline" :disabled="form.processing" @click="router.visit(base)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ form.processing ? 'Saving…' : 'Create Service' }}
                    </Button>
                </div>

            </form>
        </div>
    </ShopLayout>
</template>
