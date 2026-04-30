<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Loader2, CheckCircle2, Clock } from 'lucide-vue-next'

const props = defineProps<{
    trialDays: number
    user: { name: string; email: string }
    shop: {
        shop_name: string
        phone: string
        block_street: string
        municipality: string
        barangay: string
        postal_code: string
    }
}>()

const form = useForm({
    owner_name:   props.user.name,
    email:        props.user.email,
    shop_name:    props.shop.shop_name,
    phone:        props.shop.phone,
    block_street: props.shop.block_street,
    municipality: props.shop.municipality,
    barangay:     props.shop.barangay,
    postal_code:  props.shop.postal_code,
})

const TRIAL_MODULES = ['HRM', 'Operations', 'Inventory Management', 'Finance Management']

const submit = () => form.post('/trial')
</script>

<template>
    <Head title="Start Free Trial" />

    <div class="min-h-screen bg-gray-50 flex items-start justify-center p-4 py-12">
        <div class="w-full max-w-lg">

            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-4 py-1.5 text-sm text-emerald-700 font-medium mb-4">
                    <Clock class="h-3.5 w-3.5" />
                    {{ trialDays }}-Day Free Trial
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Set up your shop</h1>
                <p class="mt-1 text-sm text-gray-500">No credit card required. Cancel anytime.</p>
            </div>

            <!-- What's included -->
            <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700 mb-3">What's included in your trial</p>
                <ul class="space-y-1.5">
                    <li v-for="mod in TRIAL_MODULES" :key="mod" class="flex items-center gap-2 text-sm text-emerald-800">
                        <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                        {{ mod }}
                    </li>
                </ul>
            </div>

            <!-- Form card -->
            <div class="rounded-2xl border bg-white shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Account info (read-only) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label>Owner Name</Label>
                            <Input v-model="form.owner_name" readonly class="bg-gray-50 cursor-not-allowed" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Email</Label>
                            <Input v-model="form.email" readonly class="bg-gray-50 cursor-not-allowed" />
                        </div>
                    </div>

                    <div class="border-t" />

                    <!-- Shop name -->
                    <div class="space-y-1.5">
                        <Label for="shop_name">Shop Name <span class="text-red-500">*</span></Label>
                        <Input
                            id="shop_name"
                            v-model="form.shop_name"
                            placeholder="e.g. QuickWash Laundry"
                            :class="{ 'border-red-400': form.errors.shop_name }"
                        />
                        <p v-if="form.errors.shop_name" class="text-xs text-red-500">{{ form.errors.shop_name }}</p>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1.5">
                        <Label for="phone">Phone Number <span class="text-red-500">*</span></Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            placeholder="e.g. 09171234567"
                            :class="{ 'border-red-400': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Address -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="municipality">Municipality / City <span class="text-red-500">*</span></Label>
                            <Input
                                id="municipality"
                                v-model="form.municipality"
                                placeholder="e.g. Dasmariñas"
                                :class="{ 'border-red-400': form.errors.municipality }"
                            />
                            <p v-if="form.errors.municipality" class="text-xs text-red-500">{{ form.errors.municipality }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="barangay">Barangay <span class="text-red-500">*</span></Label>
                            <Input
                                id="barangay"
                                v-model="form.barangay"
                                placeholder="e.g. Salawag"
                                :class="{ 'border-red-400': form.errors.barangay }"
                            />
                            <p v-if="form.errors.barangay" class="text-xs text-red-500">{{ form.errors.barangay }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="block_street">Block / Street <span class="text-muted-foreground text-xs">(optional)</span></Label>
                            <Input
                                id="block_street"
                                v-model="form.block_street"
                                placeholder="e.g. Block 5 Lot 2"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="postal_code">Postal Code <span class="text-red-500">*</span></Label>
                            <Input
                                id="postal_code"
                                v-model="form.postal_code"
                                placeholder="e.g. 4114"
                                :class="{ 'border-red-400': form.errors.postal_code }"
                            />
                            <p v-if="form.errors.postal_code" class="text-xs text-red-500">{{ form.errors.postal_code }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2">
                        <Button type="button" variant="outline" class="flex-1" :disabled="form.processing" @click="router.visit('/shop/dashboard')">
                            <ArrowLeft class="h-4 w-4 mr-1" /> Back
                        </Button>
                        <Button type="submit" class="flex-2 flex-grow" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                            {{ form.processing ? 'Starting trial...' : `Start ${trialDays}-Day Free Trial` }}
                        </Button>
                    </div>

                </form>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Already have an account?
                <a class="text-blue-600 hover:underline cursor-pointer" @click.prevent="router.visit('/login')">Log in</a>
            </p>

        </div>
    </div>
</template>
