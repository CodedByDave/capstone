<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

// -------------------- Logged-in user --------------------
const { props } = usePage<{ auth: { user: any }, modules: any[] }>()
const user = props.auth.user

// -------------------- Steps --------------------
const currentStep  = ref(1)
const totalSteps   = 2
const isSubmitting = ref(false)

// -------------------- Modules from DB --------------------
const moduleOptions = ref(props.modules)

// -------------------- Subscription plan --------------------
const subscriptionPlan = ref<'monthly' | 'annually'>('monthly')
const ANNUAL_DISCOUNT   = 0.10

function selectPlan(plan: 'monthly' | 'annually') {
    subscriptionPlan.value = plan
}

// -------------------- Form fields (individual refs — no spread issues) --------------------
const branch_name  = ref('')
const shop_name    = ref('')
const owner_name   = ref('')
const email        = ref(user.email)
const phone        = ref('')
const block_street = ref('')
const municipality = ref('')
const barangay     = ref('')
const postal_code  = ref('')
const selectedModules = ref<number[]>([])

// -------------------- Validation errors --------------------
const errors = ref({
    shop_name:    '',
    owner_name:   '',
    phone:        '',
    block_street: '',
    modules:      '',
})

// -------------------- Computed prices --------------------
const baseTotal = computed(() =>
    selectedModules.value.reduce((sum, moduleId) => {
        const mod = moduleOptions.value.find(m => m.id === moduleId)
        return sum + parseFloat(mod?.price || 0)
    }, 0)
)

const discountAmount = computed(() =>
    subscriptionPlan.value === 'annually' ? baseTotal.value * ANNUAL_DISCOUNT : 0
)

const totalPrice = computed(() =>
    subscriptionPlan.value === 'annually'
        ? baseTotal.value * (1 - ANNUAL_DISCOUNT) * 12
        : baseTotal.value
)

// -------------------- Load shop data --------------------
onMounted(async () => {
    try {
        const res  = await axios.get('/shop/data')
        const shop = res.data

        branch_name.value  = shop.branch_name  || ''
        shop_name.value    = shop.shop_name
        owner_name.value   = user.name
        phone.value        = shop.phone
        block_street.value = shop.block_street
        municipality.value = shop.municipality
        barangay.value     = shop.barangay
        postal_code.value  = shop.postal_code
    } catch (err) {
        console.error('Failed to load shop info:', err)
    }
})

// -------------------- Validation --------------------
function validateStep1(): boolean {
    errors.value.shop_name    = shop_name.value    ? '' : 'Shop name is required.'
    errors.value.owner_name   = owner_name.value   ? '' : 'Owner name is required.'
    errors.value.phone        = /^\d{10,11}$/.test(phone.value) ? '' : 'Phone must be 10–11 digits.'
    errors.value.block_street = block_street.value ? '' : 'Block/Street is required.'
    return !Object.values(errors.value).some(e => e)
}

function validateStep2(): boolean {
    errors.value.modules = selectedModules.value.length > 0 ? '' : 'Select at least one module.'
    return !errors.value.modules
}

// -------------------- Navigation --------------------
function goNext() {
    if (currentStep.value === 1) {
        if (validateStep1()) currentStep.value = 2
        return
    }

    if (currentStep.value === 2) {
        if (!validateStep2()) return

        isSubmitting.value = true

        // Capture synchronously before async — prevents any reactivity timing issue
        const plan  = subscriptionPlan.value
        const total = totalPrice.value

        const modulesWithPrice = selectedModules.value.map(moduleId => {
            const mod = moduleOptions.value.find(m => m.id === moduleId)
            return { id: mod?.id, name: mod?.name, price: parseFloat(mod?.price || 0) }
        })

        axios.post('/shop/checkout', {
            branch_name:       branch_name.value,
            shop_name:         shop_name.value,
            owner_name:        owner_name.value,
            email:             email.value,
            phone:             phone.value,
            block_street:      block_street.value,
            municipality:      municipality.value,
            barangay:          barangay.value,
            postal_code:       postal_code.value,
            modules:           modulesWithPrice,
            subscription_plan: plan,
            payment_method:    'paymongo',
            amount:            total,
        })
            .then(res => {
                if (res.data.success && res.data.checkout_url) {
                    window.location.href = res.data.checkout_url
                } else {
                    isSubmitting.value = false
                    alert('Failed to create checkout. Please try again.')
                }
            })
            .catch(err => {
                isSubmitting.value = false
                console.error('Checkout error:', err)
                alert('An error occurred. Please try again.')
            })
    }
}

function goBack() {
    currentStep.value = 1
}

// -------------------- Module helpers --------------------
function toggleModule(moduleId: number) {
    const idx = selectedModules.value.indexOf(moduleId)
    if (idx >= 0) {
        selectedModules.value.splice(idx, 1)
    } else {
        selectedModules.value.push(moduleId)
    }
}

function removeModule(moduleId: number) {
    selectedModules.value = selectedModules.value.filter(id => id !== moduleId)
}
</script>

<template>
    <div class="flex flex-col gap-6">

        <!-- ======== STEP 1: Shop Info ======== -->
        <Card v-if="currentStep === 1">
            <CardHeader>
                <CardTitle>Shop Info – Step 1 of {{ totalSteps }}</CardTitle>
            </CardHeader>

            <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2 space-y-1">
                    <label class="text-sm font-medium">
                        Branch Name <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <Input v-model="branch_name" placeholder="e.g., Main Branch" />
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-sm font-medium">Shop Name</label>
                    <Input v-model="shop_name" placeholder="e.g., Sparkle Laundry" />
                    <p v-if="errors.shop_name" class="text-red-500 text-xs">{{ errors.shop_name }}</p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-sm font-medium">Owner Name</label>
                    <Input v-model="owner_name" placeholder="Owner full name" />
                    <p v-if="errors.owner_name" class="text-red-500 text-xs">{{ errors.owner_name }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Email</label>
                    <Input v-model="email" disabled />
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Phone</label>
                    <Input v-model="phone" placeholder="09XXXXXXXXX" />
                    <p v-if="errors.phone" class="text-red-500 text-xs">{{ errors.phone }}</p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-sm font-medium">Block / Street</label>
                    <Input v-model="block_street" placeholder="e.g., Block 5" />
                    <p v-if="errors.block_street" class="text-red-500 text-xs">{{ errors.block_street }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Municipality</label>
                    <Input v-model="municipality" disabled />
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium">Barangay</label>
                    <Input v-model="barangay" disabled />
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-sm font-medium">Postal Code</label>
                    <Input v-model="postal_code" disabled />
                </div>

            </CardContent>

            <div class="flex justify-end p-4">
                <Button type="button" @click="goNext">Next</Button>
            </div>
        </Card>

        <!-- ======== STEP 2: Plan + Modules + Billing ======== -->
        <div v-if="currentStep === 2" class="flex flex-col md:flex-row gap-6">

            <!-- Left column -->
            <div class="flex-1 flex flex-col gap-4">

                <!-- Subscription Plan Toggle -->
                <Card>
                    <CardHeader>
                        <CardTitle>Subscription Plan – Step 2 of {{ totalSteps }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-3">

                            <button
                                type="button"
                                class="flex-1 py-3 px-4 rounded-lg border-2 text-sm font-semibold transition-all text-left"
                                :class="subscriptionPlan === 'monthly'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                    : 'border-gray-200 dark:border-neutral-700 text-gray-500 hover:border-gray-300'"
                                @click="selectPlan('monthly')"
                            >
                                Monthly
                                <p class="text-xs font-normal mt-0.5 opacity-70">Full price / month</p>
                            </button>

                            <button
                                type="button"
                                class="flex-1 py-3 px-4 rounded-lg border-2 text-sm font-semibold transition-all relative text-left"
                                :class="subscriptionPlan === 'annually'
                                    ? 'border-green-500 bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300'
                                    : 'border-gray-200 dark:border-neutral-700 text-gray-500 hover:border-gray-300'"
                                @click="selectPlan('annually')"
                            >
                                <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full whitespace-nowrap">
                                    Save 10%
                                </span>
                                Annually
                                <p class="text-xs font-normal mt-0.5 opacity-70">10% off · billed yearly</p>
                            </button>

                        </div>
                    </CardContent>
                </Card>

                <!-- Modules Selection -->
                <Card>
                    <CardHeader>
                        <CardTitle>Select Modules</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-gray-500 text-xs">Choose the modules your laundry shop needs.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="mod in moduleOptions"
                                :key="mod.id"
                                class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                                :class="selectedModules.includes(mod.id)
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-950 shadow-md'
                                    : 'border-gray-200 dark:border-neutral-700'"
                                @click="toggleModule(mod.id)"
                            >
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-sm">{{ mod.name }}</span>
                                    <span class="text-green-600 font-medium text-sm">
                                        ₱{{ parseFloat(mod.price).toLocaleString() }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-xs mt-1">{{ mod.description }}</p>
                            </div>
                        </div>
                        <p v-if="errors.modules" class="text-red-500 text-xs">{{ errors.modules }}</p>
                    </CardContent>
                </Card>

            </div>

            <!-- Right: Billing Summary -->
            <Card class="w-full md:w-1/3 h-fit sticky top-6">
                <CardHeader>
                    <CardTitle>Billing Summary</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">Plan</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="subscriptionPlan === 'annually'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'"
                        >
                            {{ subscriptionPlan === 'annually' ? 'Annual' : 'Monthly' }}
                        </span>
                    </div>

                    <div v-if="selectedModules.length === 0" class="text-gray-500 text-sm">
                        No modules selected.
                    </div>

                    <div
                        v-for="moduleId in selectedModules"
                        :key="moduleId"
                        class="flex justify-between items-center p-2 border rounded"
                    >
                        <span class="text-sm">{{ moduleOptions.find(m => m.id === moduleId)?.name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-green-600 font-medium text-sm">
                                ₱{{ parseFloat(moduleOptions.find(m => m.id === moduleId)?.price || 0).toLocaleString() }}
                            </span>
                            <Button type="button" size="sm" variant="destructive" @click="removeModule(moduleId)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <div v-if="selectedModules.length > 0" class="space-y-2 border-t pt-3">

                        <div class="flex justify-between text-sm text-muted-foreground">
                            <span>Subtotal / month</span>
                            <span>₱{{ baseTotal.toLocaleString() }}</span>
                        </div>

                        <div v-if="subscriptionPlan === 'annually'" class="flex justify-between text-sm text-green-600">
                            <span>Discount (10%)</span>
                            <span>− ₱{{ discountAmount.toLocaleString() }}</span>
                        </div>

                        <div v-if="subscriptionPlan === 'annually'" class="flex justify-between text-sm text-muted-foreground">
                            <span>Billed for</span>
                            <span>12 months</span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="font-semibold">Total</span>
                            <span class="text-lg font-bold">
                                ₱{{ totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </span>
                        </div>

                        <p class="text-xs text-muted-foreground text-center">
                            {{ subscriptionPlan === 'annually' ? 'Billed once per year' : 'Billed every 30 days' }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <Button type="button" class="w-full" @click="goNext" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
                        </Button>
                        <Button type="button" variant="outline" class="w-full" @click="goBack" :disabled="isSubmitting">
                            Back
                        </Button>
                    </div>

                </CardContent>
            </Card>
        </div>

    </div>
</template>
