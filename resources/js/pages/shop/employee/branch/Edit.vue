<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed, onMounted, watch } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface PsgcItem { code: string; name: string }

interface Branch {
    id: number
    branch_code: string
    name: string
    phone: string | null
    email: string | null
    manager_name: string | null
    address: string | null
    opened_at: string | null
    status: 'Active' | 'Inactive'
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { branch } = defineProps<{ branch: Branch }>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Branch Management', href: '/shop/branch' },
    { title: 'Edit Branch', href: `/shop/branch/${branch.id}/edit` },
]

const errors = computed(() => usePage().props.errors as Record<string, string>)

// ─── PSGC cascading address ───────────────────────────────────────────────────

const BASE = 'https://psgc.cloud/api'

const provinces        = ref<PsgcItem[]>([])
const cities           = ref<PsgcItem[]>([])
const barangays        = ref<PsgcItem[]>([])
const loadingProvinces = ref(false)
const loadingCities    = ref(false)
const loadingBarangays = ref(false)
const selectedProvince = ref('')
const selectedCity     = ref('')
const selectedBarangay = ref('')
const streetInput      = ref('')

// Whether the user has started re-selecting address (to replace stored one)
const addressChanged = ref(false)

onMounted(async () => {
    loadingProvinces.value = true
    try {
        const res  = await fetch(`${BASE}/provinces`)
        const data = await res.json()
        provinces.value = data
            .map((p: any) => ({ code: p.code, name: p.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingProvinces.value = false
    }
})

watch(selectedProvince, async (code) => {
    selectedCity.value     = ''
    selectedBarangay.value = ''
    cities.value           = []
    barangays.value        = []
    if (!code) return
    addressChanged.value = true
    loadingCities.value  = true
    try {
        const [citRes, munRes] = await Promise.all([
            fetch(`${BASE}/provinces/${code}/cities`),
            fetch(`${BASE}/provinces/${code}/municipalities`),
        ])
        const citData = await citRes.json()
        const munData = await munRes.json()
        cities.value = [...(citData || []), ...(munData || [])]
            .map((c: any) => ({ code: c.code, name: c.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingCities.value = false
    }
})

watch(selectedCity, async (code) => {
    selectedBarangay.value = ''
    barangays.value        = []
    if (!code) return
    loadingBarangays.value = true
    try {
        const res  = await fetch(`${BASE}/cities-municipalities/${code}/barangays`)
        const data = await res.json()
        barangays.value = (data || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingBarangays.value = false
    }
})

// Assembled address from new PSGC selection
const newAddress = computed(() => {
    const parts = [
        streetInput.value.trim(),
        barangays.value.find(b => b.code === selectedBarangay.value)?.name ?? '',
        cities.value.find(c => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find(p => p.code === selectedProvince.value)?.name ?? '',
    ].filter(Boolean)
    return parts.join(', ')
})

// What gets submitted — new selection if changed, otherwise keep stored value
const resolvedAddress = computed(() =>
    addressChanged.value ? (newAddress.value || null) : branch.address
)

// ─── Form ─────────────────────────────────────────────────────────────────────

const form = ref({
    branch_code:  branch.branch_code,
    name:         branch.name,
    phone:        branch.phone        ?? '',
    email:        branch.email        ?? '',
    manager_name: branch.manager_name ?? '',
    opened_at:    branch.opened_at    ? branch.opened_at.slice(0, 10) : '',
    status:       branch.status,
})

const isSubmitting = ref(false)

function submit() {
    isSubmitting.value = true
    router.put(`/shop/branch/${branch.id}`, {
        ...form.value,
        address: resolvedAddress.value,
    }, {
        preserveScroll: true,
        onError: () => {
            toast.error('Failed to update branch', {
                description: 'Please check the form for errors and try again.',
            })
        },
        onFinish: () => { isSubmitting.value = false },
    })
}
</script>

<template>
    <Head title="Edit Branch" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Branch">
        <div class="px-6 space-y-8">

            <div>
                <h2 class="text-lg font-semibold">Edit Branch</h2>
                <p class="text-sm text-muted-foreground">
                    Update the details for
                    <span class="font-medium text-foreground">{{ branch.name }}</span>.
                </p>
            </div>

            <!-- ── Branch Info ───────────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Branch Info</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Branch Code <span class="text-red-500">*</span></label>
                        <Input v-model="form.branch_code" placeholder="e.g. BR-001"
                            class="font-mono uppercase" :class="{ 'border-red-500': errors.branch_code }" />
                        <p v-if="errors.branch_code" class="text-xs text-red-500">{{ errors.branch_code }}</p>
                    </div>
                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-sm font-medium">Branch Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.name" placeholder="e.g. Makati Branch"
                            :class="{ 'border-red-500': errors.name }" />
                        <p v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Manager Name</label>
                        <Input v-model="form.manager_name" placeholder="e.g. Juan dela Cruz"
                            :class="{ 'border-red-500': errors.manager_name }" />
                        <p v-if="errors.manager_name" class="text-xs text-red-500">{{ errors.manager_name }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="form.phone" placeholder="02-XXXX-XXXX or 09XX"
                            :class="{ 'border-red-500': errors.phone }" />
                        <p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Email</label>
                        <Input v-model="form.email" type="email" placeholder="branch@example.com"
                            :class="{ 'border-red-500': errors.email }" />
                        <p v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Date Opened</label>
                        <Input v-model="form.opened_at" type="date"
                            :class="{ 'border-red-500': errors.opened_at }" />
                        <p v-if="errors.opened_at" class="text-xs text-red-500">{{ errors.opened_at }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Status <span class="text-red-500">*</span></label>
                        <Select v-model="form.status">
                            <SelectTrigger :class="{ 'border-red-500': errors.status }">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Active">Active</SelectItem>
                                <SelectItem value="Inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.status" class="text-xs text-red-500">{{ errors.status }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Address ───────────────────────────────────────────────── -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Address</p>
                    <button
                        v-if="!addressChanged && branch.address"
                        type="button"
                        class="text-xs text-primary hover:underline"
                        @click="addressChanged = true"
                    >
                        Change address
                    </button>
                </div>

                <!-- Show stored address if not changing -->
                <div v-if="!addressChanged && branch.address"
                    class="rounded-lg border bg-muted/40 px-4 py-3 text-sm text-muted-foreground">
                    {{ branch.address }}
                </div>

                <!-- PSGC selectors — shown when changing or no stored address -->
                <template v-if="addressChanged || !branch.address">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Province <span class="text-red-500">*</span></label>
                            <Select v-model="selectedProvince" :disabled="loadingProvinces">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="loadingProvinces" class="flex items-center gap-1.5 text-muted-foreground">
                                            <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                        </span>
                                        <span v-else-if="!selectedProvince" class="text-muted-foreground">Select province</span>
                                        <span v-else>{{ provinces.find(p => p.code === selectedProvince)?.name }}</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent class="max-h-60">
                                    <SelectItem v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">City / Municipality <span class="text-red-500">*</span></label>
                            <Select v-model="selectedCity" :disabled="!selectedProvince || loadingCities">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="loadingCities" class="flex items-center gap-1.5 text-muted-foreground">
                                            <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                        </span>
                                        <span v-else-if="!selectedCity" class="text-muted-foreground">Select city/municipality</span>
                                        <span v-else>{{ cities.find(c => c.code === selectedCity)?.name }}</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent class="max-h-60">
                                    <SelectItem v-for="c in cities" :key="c.code" :value="c.code">{{ c.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Barangay <span class="text-red-500">*</span></label>
                            <Select v-model="selectedBarangay" :disabled="!selectedCity || loadingBarangays">
                                <SelectTrigger class="w-full">
                                    <SelectValue>
                                        <span v-if="loadingBarangays" class="flex items-center gap-1.5 text-muted-foreground">
                                            <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                        </span>
                                        <span v-else-if="!selectedBarangay" class="text-muted-foreground">Select barangay</span>
                                        <span v-else>{{ barangays.find(b => b.code === selectedBarangay)?.name }}</span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent class="max-h-60">
                                    <SelectItem v-for="b in barangays" :key="b.code" :value="b.code">{{ b.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium">Street / Building / Unit No.</label>
                            <Input v-model="streetInput" placeholder="e.g. 3F Ayala Tower, Ayala Ave." />
                        </div>
                    </div>

                    <p v-if="newAddress" class="text-xs text-muted-foreground">
                        <span class="font-medium">Full address:</span> {{ newAddress }}
                    </p>

                    <!-- Cancel address change (revert to stored) -->
                    <button
                        v-if="addressChanged && branch.address"
                        type="button"
                        class="text-xs text-muted-foreground hover:underline"
                        @click="addressChanged = false; selectedProvince = ''; selectedCity = ''; selectedBarangay = ''; streetInput = ''"
                    >
                        ← Keep existing address
                    </button>
                </template>

                <p v-if="errors.address" class="text-xs text-red-500">{{ errors.address }}</p>
            </div>

            <!-- ── Actions ───────────────────────────────────────────────── -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button type="button" variant="outline" :disabled="isSubmitting"
                    @click="router.visit('/shop/branch')">
                    Cancel
                </Button>
                <Button type="button" :disabled="isSubmitting" @click="submit">
                    <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
                    {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
