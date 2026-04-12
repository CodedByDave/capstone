<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Checkbox } from '@/components/ui/checkbox'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, onMounted, watch } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2, X } from 'lucide-vue-next'
import axios from 'axios'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number
    employee_id: string
    user_id: number | null
    first_name: string
    last_name: string
    phone: string | null
    address: string | null
    position: string
    branch_name: string | null
    hire_date: string
    salary: string | null
    status: 'Active' | 'Inactive'
}

interface Schedule {
    id: number
    day: string
    start_time: string
    end_time: string
}

interface PsgcItem {
    code: string
    name: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee, branch_names, schedules: initialSchedules } = defineProps<{
    employee: Employee
    branch_names: string[]
    schedules: Schedule[]
    roles: string[]
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const user = computed(() => page.props.auth.user)
const isOwner = computed(() => user.value.role === 'owner')
const isStaff = computed(() => !isOwner.value)
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: `${employee.first_name} ${employee.last_name}`, href: `${baseRoute.value}/employee/${employee.id}` },
    { title: 'Edit', href: `${baseRoute.value}/employee/${employee.id}/edit` },
])

const errors = computed(() => page.props.errors as Record<string, string>)

// ─── PSGC Address ─────────────────────────────────────────────────────────────

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

function parseExistingAddress(address: string | null) {
    if (!address) return
    streetInput.value = address.split(',')[0]?.trim() ?? ''
}

onMounted(async () => {
    parseExistingAddress(employee.address)
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
})

watch(selectedProvince, async (code) => {
    selectedCity.value = ''; selectedBarangay.value = ''
    cities.value = []; barangays.value = []
    if (!code) return
    loadingCities.value = true
    try {
        const [citRes, munRes] = await Promise.all([
            fetch(`${BASE}/provinces/${code}/cities`),
            fetch(`${BASE}/provinces/${code}/municipalities`),
        ])
        cities.value = [...(await citRes.json() || []), ...(await munRes.json() || [])]
            .map((c: any) => ({ code: c.code, name: c.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingCities.value = false
    }
})

watch(selectedCity, async (code) => {
    selectedBarangay.value = ''; barangays.value = []
    if (!code) return
    loadingBarangays.value = true
    try {
        const res = await fetch(`${BASE}/cities-municipalities/${code}/barangays`)
        barangays.value = (await res.json() || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } finally {
        loadingBarangays.value = false
    }
})

const fullAddress = computed(() => {
    const hasNewSelection = selectedBarangay.value || selectedCity.value || selectedProvince.value
    if (!hasNewSelection) {
        if (!employee.address) return streetInput.value.trim()
        const parts = employee.address.split(',').map(s => s.trim())
        parts[0] = streetInput.value.trim()
        return parts.filter(Boolean).join(', ')
    }
    return [
        streetInput.value.trim(),
        barangays.value.find(b => b.code === selectedBarangay.value)?.name ?? '',
        cities.value.find(c => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find(p => p.code === selectedProvince.value)?.name ?? '',
    ].filter(Boolean).join(', ')
})

// ─── Employee Form ────────────────────────────────────────────────────────────

const hasAccount = employee.user_id !== null && employee.user_id !== undefined

// ← Separate ref so shadcn Checkbox reactivity is isolated and reliable
const createAccount = ref(false)

const form = ref({
    employee_id: employee.employee_id,
    branch_name: employee.branch_name ?? '',
    first_name: employee.first_name,
    last_name: employee.last_name,
    phone: employee.phone ?? '',
    address: employee.address ?? '',
    position: employee.position,
    hire_date: employee.hire_date,
    salary: employee.salary ?? '',
    status: employee.status,
})

const isSubmitting = ref(false)

function submit() {
    form.value.address = fullAddress.value
    isSubmitting.value = true
    router.put(`${baseRoute.value}/employee/${employee.id}`, {
        ...form.value,
        branch_name: form.value.branch_name === '__none__' ? '' : form.value.branch_name,
        create_account: createAccount.value ? 1 : 0,
    }, {
        preserveScroll: true,
        onFinish: () => { isSubmitting.value = false },
    })
}

// ─── Schedule ─────────────────────────────────────────────────────────────────

const weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as const

interface DaySchedule {
    id?: number
    day: string
    start_time: string
    end_time: string
    enabled: boolean
}

const scheduleMap = ref<DaySchedule[]>(
    weekdays.map(day => {
        const existing = initialSchedules?.find(s => s.day === day)
        return {
            id: existing?.id,
            day,
            start_time: existing?.start_time?.slice(0, 5) ?? '08:00',
            end_time: existing?.end_time?.slice(0, 5) ?? '17:00',
            enabled: !!existing,
        }
    })
)

const savingSchedule = ref(false)
const scheduleError = ref<string | null>(null)

async function saveSchedule() {
    const active = scheduleMap.value.filter(d => d.enabled)
    const invalid = active.find(d => d.start_time >= d.end_time)
    if (invalid) {
        scheduleError.value = `${invalid.day}: end time must be after start time.`
        return
    }

    savingSchedule.value = true
    scheduleError.value = null
    try {
        await axios.put(`/shop/employee/${employee.id}/schedule`, {
            schedules: active.map(d => ({
                day: d.day,
                start_time: d.start_time,
                end_time: d.end_time,
            })),
        })
        toast.success(`Schedule successfully added to ${employee.first_name} ${employee.last_name}.`)
    } catch (err: any) {
        scheduleError.value = err.response?.data?.message ?? 'Failed to save schedule.'
        toast.error(scheduleError.value!)
    } finally {
        savingSchedule.value = false
    }
}
</script>

<template>

    <Head :title="`Edit — ${employee.first_name} ${employee.last_name}`" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Employee">
        <div class="px-6 space-y-8">

            <!-- Page title -->
            <div>
                <h2 class="text-lg font-semibold">Edit Employee</h2>
                <p class="text-sm text-muted-foreground">
                    Update the details for
                    <span class="font-medium text-foreground">{{ employee.first_name }} {{ employee.last_name }}</span>.
                </p>
            </div>

            <!-- ── Identity ──────────────────────────────────────────────────── -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Identity</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Employee ID</label>
                        <Input v-model="form.employee_id" class="font-mono" disabled />
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">First Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.first_name" :class="{ 'border-red-500': errors.first_name }" />
                        <p v-if="errors.first_name" class="text-xs text-red-500">{{ errors.first_name }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Last Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.last_name" :class="{ 'border-red-500': errors.last_name }" />
                        <p v-if="errors.last_name" class="text-xs text-red-500">{{ errors.last_name }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="form.phone" placeholder="09XXXXXXXXX"
                            :class="{ 'border-red-500': errors.phone }" />
                        <p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Employment ────────────────────────────────────────────────── -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Employment</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Position <span class="text-red-500">*</span></label>
                        <Select v-model="form.position">
                            <SelectTrigger class="w-full" :class="{ 'border-red-500': errors.position }">
                                <SelectValue placeholder="Select position" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in roles" :key="role" :value="role" class="capitalize">
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.position" class="text-xs text-red-500">{{ errors.position }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
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

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Hire Date <span class="text-red-500">*</span>
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div v-if="isStaff"
                            class="flex h-9 w-full rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed select-none">
                            {{ form.hire_date }}
                        </div>
                        <Input v-else v-model="form.hire_date" type="date"
                            :class="{ 'border-red-500': errors.hire_date }" />
                        <p v-if="errors.hire_date" class="text-xs text-red-500">{{ errors.hire_date }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Salary (₱)
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div v-if="isStaff"
                            class="flex h-9 w-full rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed select-none">
                            {{ form.salary ? `₱${Number(form.salary).toLocaleString('en-PH', {
                                minimumFractionDigits: 2
                            })}` : '—' }}
                        </div>
                        <Input v-else v-model="form.salary" type="number" min="0" step="0.01"
                            :class="{ 'border-red-500': errors.salary }" />
                        <p v-if="errors.salary" class="text-xs text-red-500">{{ errors.salary }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Status <span class="text-red-500">*</span>
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div v-if="isStaff"
                            class="flex h-9 w-full items-center rounded-md border border-input bg-muted/50 px-3 py-2 text-sm cursor-not-allowed select-none">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                                :class="form.status === 'Active' ? 'bg-green-500' : 'bg-red-500'">
                                {{ form.status }}
                            </span>
                        </div>
                        <Select v-else v-model="form.status">
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

            <!-- ── Address ───────────────────────────────────────────────────── -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Address</p>
                <div v-if="employee.address" class="mb-4 rounded-lg border bg-muted/40 px-4 py-3 text-sm">
                    <p class="text-xs text-muted-foreground mb-1">Current address</p>
                    <p class="font-medium">{{ employee.address }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Select a new province below to update, or just edit
                        the street
                        field.</p>
                </div>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Province</label>
                        <Select v-model="selectedProvince" :disabled="loadingProvinces">
                            <SelectTrigger>
                                <SelectValue>
                                    <span v-if="loadingProvinces"
                                        class="flex items-center gap-1.5 text-muted-foreground">
                                        <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                    </span>
                                    <span v-else-if="!selectedProvince" class="text-muted-foreground">Select
                                        province</span>
                                    <span v-else>{{provinces.find(p => p.code === selectedProvince)?.name}}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">City / Municipality</label>
                        <Select v-model="selectedCity" :disabled="!selectedProvince || loadingCities">
                            <SelectTrigger>
                                <SelectValue>
                                    <span v-if="loadingCities" class="flex items-center gap-1.5 text-muted-foreground">
                                        <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                    </span>
                                    <span v-else-if="!selectedCity" class="text-muted-foreground">Select
                                        city/municipality</span>
                                    <span v-else>{{cities.find(c => c.code === selectedCity)?.name}}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem v-for="c in cities" :key="c.code" :value="c.code">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Barangay</label>
                        <Select v-model="selectedBarangay" :disabled="!selectedCity || loadingBarangays">
                            <SelectTrigger>
                                <SelectValue>
                                    <span v-if="loadingBarangays"
                                        class="flex items-center gap-1.5 text-muted-foreground">
                                        <Loader2 class="h-3 w-3 animate-spin" /> Loading...
                                    </span>
                                    <span v-else-if="!selectedBarangay" class="text-muted-foreground">Select
                                        barangay</span>
                                    <span v-else>{{barangays.find(b => b.code === selectedBarangay)?.name}}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem v-for="b in barangays" :key="b.code" :value="b.code">{{ b.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Street / Block No.</label>
                        <Input v-model="streetInput" placeholder="e.g. 123 Rizal St." />
                    </div>
                </div>
                <p v-if="fullAddress" class="mt-3 text-xs text-muted-foreground">
                    <span class="font-medium">Full address:</span> {{ fullAddress }}
                </p>
                <p v-if="errors.address" class="mt-1 text-xs text-red-500">{{ errors.address }}</p>
            </div>

            <!-- ── System Access ─────────────────────────────────────────────── -->
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">System Access</p>

                <div v-if="hasAccount"
                    class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 px-4 py-3">
                    <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0" />
                    <p class="text-sm text-green-800 dark:text-green-200">
                        This employee already has a system account.
                    </p>
                </div>

                <template v-else>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="create_account" :checked="createAccount"
                            @change="(e) => { createAccount = e.target.checked; }"
                            class="h-4 w-4 mt-1 rounded border-gray-300 cursor-pointer" />
                        <div class="space-y-1">
                            <label for="create_account" class="text-sm font-medium cursor-pointer">
                                Create a system account for this employee
                            </label>
                            <p class="text-xs text-muted-foreground">
                                A login account will be created using the employee's email. The default password will be
                                their last name. Only enable this for employees who need system access.
                            </p>
                        </div>
                    </div>
                    <p v-if="errors.create_account" class="text-xs text-red-500">{{ errors.create_account }}</p>
                </template>
            </div>

            <!-- ── Schedule (owner only) ──────────────────────────────────────── -->
            <div v-if="isOwner">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Weekly Schedule
                        </p>
                        <p class="text-xs text-muted-foreground mt-0.5">Toggle workdays and set shift times.</p>
                    </div>
                    <Button size="sm" :disabled="savingSchedule" @click="saveSchedule">
                        <Loader2 v-if="savingSchedule" class="h-3.5 w-3.5 mr-1.5 animate-spin" />
                        {{ savingSchedule ? 'Saving...' : 'Save Schedule' }}
                    </Button>
                </div>

                <div v-if="scheduleError"
                    class="mb-3 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                    <span>{{ scheduleError }}</span>
                    <button @click="scheduleError = null">
                        <X class="h-3.5 w-3.5 ml-3 text-red-400 hover:text-red-600" />
                    </button>
                </div>

                <div class="rounded-xl border divide-y">
                    <div v-for="d in scheduleMap" :key="d.day"
                        class="flex items-center gap-4 px-4 py-3 transition-colors"
                        :class="d.enabled ? 'bg-background' : 'bg-muted/30'">
                        <label class="flex items-center gap-3 w-32 cursor-pointer select-none">
                            <input type="checkbox" v-model="d.enabled"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                            <span class="text-sm font-medium" :class="{ 'text-muted-foreground': !d.enabled }">
                                {{ d.day }}
                            </span>
                        </label>
                        <template v-if="d.enabled">
                            <div class="flex items-center gap-2">
                                <Input v-model="d.start_time" type="time" class="h-8 w-32 text-sm" />
                                <span class="text-xs text-muted-foreground">to</span>
                                <Input v-model="d.end_time" type="time" class="h-8 w-32 text-sm" />
                            </div>
                        </template>
                        <span v-else class="text-sm text-muted-foreground">Day off</span>
                    </div>
                </div>
            </div>

            <!-- ── Actions ─────────────────────────────────────────────────────── -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button type="button" variant="outline" :disabled="isSubmitting"
                    @click="router.visit(`${baseRoute}/employee`)">
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
