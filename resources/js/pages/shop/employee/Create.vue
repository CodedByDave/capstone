<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed, onMounted, watch } from 'vue'
import { toast } from 'vue-sonner'

// shadcn components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// icons
import { RefreshCw, Loader2 } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    id: number
    shop_name: string
    branch_name: string | null
}

interface PsgcItem {
    code: string
    name: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { branch_names, shop } = defineProps<{
    branch_names: string[]
    shop: Shop
}>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Add Employee', href: '/shop/employee/create' },
]

// ─── Validation errors ────────────────────────────────────────────────────────

const errors = computed(() => usePage().props.errors as Record<string, string>)

// ─── Auto-generate Employee ID ────────────────────────────────────────────────

function generateEmployeeId(): string {
    const year = new Date().getFullYear()
    const digits = String(Math.floor(10000 + Math.random() * 90000))
    return `${year}-${digits}`
}

// ─── PSGC Address State ───────────────────────────────────────────────────────

const BASE = 'https://psgc.cloud/api'

const provinces = ref<PsgcItem[]>([])
const cities = ref<PsgcItem[]>([])
const barangays = ref<PsgcItem[]>([])

const loadingProvinces = ref(false)
const loadingCities = ref(false)
const loadingBarangays = ref(false)

const selectedProvince = ref('')
const selectedCity = ref('')
const selectedBarangay = ref('')
const streetInput = ref('')

onMounted(async () => {
    loadingProvinces.value = true
    try {
        const res = await fetch(`${BASE}/provinces`)
        const data = await res.json()
        provinces.value = data
            .map((p: any) => ({ code: p.code, name: p.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingProvinces.value = false
    }
    form.value.employee_id = generateEmployeeId()
})

watch(selectedProvince, async (code) => {
    selectedCity.value = ''
    selectedBarangay.value = ''
    cities.value = []
    barangays.value = []
    if (!code) return
    loadingCities.value = true
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
    barangays.value = []
    if (!code) return
    loadingBarangays.value = true
    try {
        const res = await fetch(`${BASE}/cities-municipalities/${code}/barangays`)
        const data = await res.json()
        barangays.value = (data || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingBarangays.value = false
    }
})

const fullAddress = computed(() => {
    const parts = [
        streetInput.value.trim(),
        barangays.value.find(b => b.code === selectedBarangay.value)?.name ?? '',
        cities.value.find(c => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find(p => p.code === selectedProvince.value)?.name ?? '',
    ].filter(Boolean)
    return parts.join(', ')
})

// ─── Form ─────────────────────────────────────────────────────────────────────

const form = ref({
    employee_id: '',
    branch_name: shop.branch_name ?? '',
    first_name: '',
    last_name: '',
    phone: '',
    address: '',
    position: '',
    hire_date: '',
    salary: '',
    status: 'Active' as 'Active' | 'Inactive',
})

// ─── Submit ───────────────────────────────────────────────────────────────────

const isSubmitting = ref(false)

function cancel() {
    router.visit('/shop/employee')
}

function submit() {
    form.value.address = fullAddress.value
    isSubmitting.value = true
    const payload = {
        ...form.value,
        branch_name: form.value.branch_name === '__none__' ? '' : form.value.branch_name,
    }
    router.post('/shop/employee', payload, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Employee Added', {
                description: `${form.value.first_name} ${form.value.last_name} has been registered successfully.`,
            })
            router.visit('/shop/employee')
        },
        onError: () => {
            toast.error('Failed to add employee', {
                description: 'Please check the form for errors and try again.',
            })
        },
        onFinish: () => {
            isSubmitting.value = false
        },
    })
}
</script>

<template>

    <Head title="Add New Employee" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Add New Employee">

        <div class="px-6 space-y-8">

            <!-- Page title -->
            <div>
                <h2 class="text-lg font-semibold">New Employee</h2>
                <p class="text-sm text-muted-foreground">
                    Fill in the details below to register a new employee under
                    <span class="font-medium text-foreground">{{ shop.shop_name }}</span>.
                </p>
            </div>

            <!-- ── Section 1: Identity ─────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Identity</p>

                <!-- Row 1: Employee ID | First Name | Last Name | Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Employee ID <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <Input v-model="form.employee_id" class="font-mono"
                                :class="{ 'border-red-500': errors.employee_id }" />
                            <Button type="button" variant="outline" size="icon"
                                @click="form.employee_id = generateEmployeeId()" title="Regenerate">
                                <RefreshCw class="h-4 w-4" />
                            </Button>
                        </div>
                        <p v-if="errors.employee_id" class="text-xs text-red-500">{{ errors.employee_id }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">First Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.first_name" :class="{ 'border-red-500': errors.first_name }" />
                        <p v-if="errors.first_name" class="text-xs text-red-500">{{ errors.first_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Last Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.last_name" :class="{ 'border-red-500': errors.last_name }" />
                        <p v-if="errors.last_name" class="text-xs text-red-500">{{ errors.last_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="form.phone" placeholder="09XXXXXXXXX"
                            :class="{ 'border-red-500': errors.phone }" />
                        <p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
                    </div>

                </div>
            </div>

            <!-- ── Section 2: Employment ──────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Employment</p>

                <!-- Row 1: Position | Branch | Hire Date | Salary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Position <span class="text-red-500">*</span></label>
                        <Input v-model="form.position" placeholder="e.g. Cashier"
                            :class="{ 'border-red-500': errors.position }" />
                        <p v-if="errors.position" class="text-xs text-red-500">{{ errors.position }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Branch</label>
                        <template v-if="branch_names.length > 0">
                            <Select v-model="form.branch_name">
                                <SelectTrigger :class="{ 'border-red-500': errors.branch_name }">
                                    <SelectValue placeholder="Select branch" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__">— No Branch —</SelectItem>
                                    <SelectItem v-for="b in branch_names" :key="b" :value="b">{{ b }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </template>
                        <template v-else>
                            <Input v-model="form.branch_name" placeholder="e.g. Main Branch"
                                :class="{ 'border-red-500': errors.branch_name }" />
                        </template>
                        <p v-if="errors.branch_name" class="text-xs text-red-500">{{ errors.branch_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Hire Date <span class="text-red-500">*</span></label>
                        <Input v-model="form.hire_date" type="date"
                            :class="{ 'border-red-500': errors.hire_date }" />
                        <p v-if="errors.hire_date" class="text-xs text-red-500">{{ errors.hire_date }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Salary (₱)</label>
                        <Input v-model="form.salary" type="number" min="0" step="0.01"
                            :class="{ 'border-red-500': errors.salary }" />
                        <p v-if="errors.salary" class="text-xs text-red-500">{{ errors.salary }}</p>
                    </div>

                </div>

                <!-- Row 2: Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

            <!-- ── Section 3: Address ─────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Address</p>

                <!-- Row 1: Province | City | Barangay | Street -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Province <span class="text-red-500">*</span></label>
                        <Select v-model="selectedProvince" :disabled="loadingProvinces">
                            <SelectTrigger>
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
                            <SelectTrigger>
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
                            <SelectTrigger>
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

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Street / Block No.</label>
                        <Input v-model="streetInput" placeholder="e.g. 123 Rizal St." />
                    </div>

                </div>

                <p v-if="fullAddress" class="text-xs text-muted-foreground">
                    <span class="font-medium">Full address:</span> {{ fullAddress }}
                </p>
                <p v-if="errors.address" class="text-xs text-red-500">{{ errors.address }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button type="button" variant="outline" :disabled="isSubmitting" @click="cancel">
                    Cancel
                </Button>
                <Button type="button" :disabled="isSubmitting" @click="submit">
                    <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
                    {{ isSubmitting ? 'Saving...' : 'Add Employee' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
