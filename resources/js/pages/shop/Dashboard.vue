<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// -------------------- Breadcrumbs --------------------
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url }
]

// -------------------- Logged-in user --------------------
const { props } = usePage<{ auth: { user: any }, modules: any[] }>()
const user = props.auth.user

// -------------------- Steps --------------------
const currentStep = ref(1)
const totalSteps = 2
const isSubmitting = ref(false)

// -------------------- Modules from DB --------------------
const moduleOptions = ref(props.modules) // Use DB modules

// -------------------- Order Form --------------------
const orderForm = ref({
    branch_name: '',
    shop_name: '',
    owner_name: '',
    email: user.email,
    phone: '',
    block_street: '',
    municipality: '',
    barangay: '',
    postal_code: '',
    modules: [] as number[] // store module IDs
})

// -------------------- Validation errors --------------------
const errors = ref({
    branch_name: '',
    shop_name: '',
    owner_name: '',
    email: '',
    phone: '',
    block_street: '',
    modules: ''
})

// -------------------- Computed total price --------------------
const totalPrice = computed(() => {
    return orderForm.value.modules.reduce((sum, moduleId) => {
        const module = moduleOptions.value.find(m => m.id === moduleId)
        return sum + (module?.price || 0)
    }, 0)
})

// -------------------- Load shop data from backend --------------------
onMounted(async () => {
    try {
        const res = await axios.get('/shop/data')
        const shop = res.data

        orderForm.value.branch_name = shop.branch_name || ''
        orderForm.value.shop_name = shop.shop_name
        orderForm.value.owner_name = user.name
        orderForm.value.phone = shop.phone
        orderForm.value.block_street = shop.block_street
        orderForm.value.municipality = shop.municipality
        orderForm.value.barangay = shop.barangay
        orderForm.value.postal_code = shop.postal_code
    } catch (err) {
        console.error('Failed to load shop info:', err)
    }
})

// -------------------- Step validation --------------------
function validateCurrentStep() {
    if (currentStep.value === 1) {
        errors.value.shop_name = orderForm.value.shop_name ? '' : 'Shop Name is required.'
        errors.value.owner_name = orderForm.value.owner_name ? '' : 'Owner Name is required.'
        errors.value.phone = /^\d{10,11}$/.test(orderForm.value.phone) ? '' : 'Phone must be 10-11 digits.'
        errors.value.block_street = orderForm.value.block_street ? '' : 'Block/Street is required.'
    }
    if (currentStep.value === 2) {
        errors.value.modules = orderForm.value.modules.length > 0 ? '' : 'Select at least one module.'
    }
}

// -------------------- Go next / submit --------------------
function goNext() {
    validateCurrentStep()

    if (currentStep.value === 1 && !Object.values(errors.value).some(e => e)) {
        currentStep.value++
        return
    }

    if (currentStep.value === 2 && !Object.values(errors.value).some(e => e)) {
        isSubmitting.value = true

        const modulesWithPrice = orderForm.value.modules.map(moduleId => {
            const module = moduleOptions.value.find(m => m.id === moduleId)
            return { id: module?.id, name: module?.name, price: module?.price || 0 }
        })

        axios.post('/shop/checkout', {
            ...orderForm.value,
            modules: modulesWithPrice,
            payment_method: 'paymongo',
            amount: totalPrice.value
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
                alert('Failed to create checkout. Please try again.')
            })
    }
}

// -------------------- Remove module --------------------
function removeModule(moduleId: number) {
    orderForm.value.modules = orderForm.value.modules.filter(id => id !== moduleId)
}

// Toggle module selection by clicking card
function toggleModule(moduleId: number) {
    const idx = orderForm.value.modules.indexOf(moduleId)
    if (idx >= 0) {
        orderForm.value.modules.splice(idx, 1)
    } else {
        orderForm.value.modules.push(moduleId)
    }
}

</script>

<template>

    <Head title="Shop Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- Welcome -->
            <div class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Welcome {{ user.name }} 👋
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-2xl">
                    Complete your shop details and select the modules you need. You will be redirected to Stripe for
                    payment.
                </p>
            </div>

            <!-- Step 1: Shop Info -->
            <Card v-if="currentStep === 1">
                <CardHeader>
                    <CardTitle>Shop Info – Step 1 of {{ totalSteps }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Branch Name <span
                                class="text-gray-400 text-xs">(Optional)</span></label>
                        <Input v-model="orderForm.branch_name" placeholder="e.g., Main Branch" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Shop Name</label>
                        <Input v-model="orderForm.shop_name" placeholder="e.g., Sparkle Laundry" />
                        <p v-if="errors.shop_name" class="text-red-500 text-xs mt-1">{{ errors.shop_name }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Owner Name</label>
                        <Input v-model="orderForm.owner_name" placeholder="Owner full name" />
                        <p v-if="errors.owner_name" class="text-red-500 text-xs mt-1">{{ errors.owner_name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Email</label>
                        <Input v-model="orderForm.email" disabled />
                    </div>

                    <div>
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="orderForm.phone" placeholder="09XXXXXXXXX" />
                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Block / Street</label>
                        <Input v-model="orderForm.block_street" placeholder="e.g., Block 5" />
                        <p v-if="errors.block_street" class="text-red-500 text-xs mt-1">{{ errors.block_street }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Municipality</label>
                        <Input v-model="orderForm.municipality" disabled />
                    </div>

                    <div>
                        <label class="text-sm font-medium">Barangay</label>
                        <Input v-model="orderForm.barangay" disabled />
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Postal Code</label>
                        <Input v-model="orderForm.postal_code" disabled />
                    </div>

                </CardContent>
                <div class="flex justify-end p-4">
                    <Button @click="goNext" :disabled="isSubmitting">Next</Button>
                </div>
            </Card>

            <!-- Step 2: Modules + Billing -->
            <div v-if="currentStep === 2" class="flex flex-col md:flex-row gap-6">

                <!-- Modules Selection -->
                <Card class="flex-1">
                    <CardHeader>
                        <CardTitle>Select Modules – Step 2 of {{ totalSteps }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-gray-500 text-xs mt-1">Choose the modules your laundry shop needs.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="module in moduleOptions" :key="module.id"
                                class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                                :class="orderForm.modules.includes(module.id) ? 'border-blue-500 bg-blue-50 dark:bg-blue-950 shadow-md' : 'border-gray-200 dark:border-neutral-700'"
                                @click="toggleModule(module.id)">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">{{ module.name }}</span>
                                    <span class="text-green-600 font-medium">₱{{ module.price.toLocaleString() }}</span>
                                </div>
                                <p class="text-gray-500 text-sm mt-1">{{ module.description }}</p>
                            </div>
                        </div>
                        <p v-if="errors.modules" class="text-red-500 text-xs mt-1">{{ errors.modules }}</p>
                    </CardContent>
                </Card>

                <!-- Billing Summary -->
                <Card class="w-full md:w-1/3">
                    <CardHeader>
                        <CardTitle>Billing Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="orderForm.modules.length === 0" class="text-gray-500 text-sm">
                            No modules selected.
                        </div>
                        <div v-for="moduleId in orderForm.modules" :key="moduleId"
                            class="flex justify-between items-center p-2 border rounded">
                            <span>{{moduleOptions.find(m => m.id === moduleId)?.name}}</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-green-600 font-medium">₱{{(moduleOptions.find(m => m.id ===
                                    moduleId)?.price || 0).toLocaleString() }}</span>
                                <Button size="sm" variant="destructive" @click="removeModule(moduleId)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-if="orderForm.modules.length > 0"
                            class="mt-3 p-3 bg-gray-100 dark:bg-neutral-800 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Total Price:</span>
                                <span class="text-lg font-bold text-dark-600">₱{{ totalPrice.toLocaleString() }}</span>
                            </div>
                        </div>

                        <div>
                            <Button class="mt-3 w-full" @click="goNext" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

        </div>
    </ShopLayout>
</template>
