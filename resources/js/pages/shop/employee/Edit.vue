<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, onMounted, watch } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2, Calendar, Plus, Trash2, Pencil, Check, X } from 'lucide-vue-next'
import axios from 'axios'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number
    employee_id: string
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
    work_date: string
    start_time: string
    end_time: string
}

interface PsgcItem {
    code: string
    name: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee, branch_names, schedules: initialSchedules } = defineProps<{
    employee:     Employee
    branch_names: string[]
    schedules:    Schedule[]
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page      = usePage<AppPageProps>()
const user      = computed(() => page.props.auth.user)
const isOwner   = computed(() => user.value.role === 'owner')
const isStaff   = computed(() => !isOwner.value)
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
const cities    = ref<PsgcItem[]>([])
const barangays = ref<PsgcItem[]>([])

const loadingProvinces = ref(false)
const loadingCities    = ref(false)
const loadingBarangays = ref(false)

const selectedProvince = ref('')
const selectedCity     = ref('')
const selectedBarangay = ref('')
const streetInput      = ref('')

function parseExistingAddress(address: string | null) {
    if (!address) return
    streetInput.value = address.split(',')[0]?.trim() ?? ''
}

onMounted(async () => {
    parseExistingAddress(employee.address)
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
        const res  = await fetch(`${BASE}/cities-municipalities/${code}/barangays`)
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

const form = ref({
    employee_id: employee.employee_id,
    branch_name: employee.branch_name ?? '',
    first_name:  employee.first_name,
    last_name:   employee.last_name,
    phone:       employee.phone ?? '',
    address:     employee.address ?? '',
    position:    employee.position,
    hire_date:   employee.hire_date,
    salary:      employee.salary ?? '',
    status:      employee.status,
})

const isSubmitting = ref(false)

function submit() {
    form.value.address = fullAddress.value
    isSubmitting.value = true
    router.put(`${baseRoute.value}/employee/${employee.id}`, {
        ...form.value,
        branch_name: form.value.branch_name === '__none__' ? '' : form.value.branch_name,
    }, {
        preserveScroll: true,
        onFinish: () => { isSubmitting.value = false },
    })
}

// ─── Schedule ─────────────────────────────────────────────────────────────────

const scheduleList  = ref<Schedule[]>([...(initialSchedules ?? [])])
const scheduleError = ref<string | null>(null)

const showNewRow  = ref(false)
const newRow      = ref({ work_date: '', start_time: '', end_time: '' })
const savingNew   = ref(false)

const editingId  = ref<number | null>(null)
const editRow    = ref({ work_date: '', start_time: '', end_time: '' })
const savingEdit = ref(false)
const deletingId = ref<number | null>(null)

function fmt(time: string): string {
    if (!time) return ''
    const [h, m] = time.slice(0, 5).split(':').map(Number)
    const period = h >= 12 ? 'PM' : 'AM'
    const hour   = h % 12 || 12
    return `${hour}:${m.toString().padStart(2, '0')} ${period}`
}

function fmtDate(date: string) {
    return new Date(date + 'T00:00:00').toLocaleDateString('en-PH', {
        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
    })
}

async function addSchedule() {
    if (!newRow.value.work_date || !newRow.value.start_time || !newRow.value.end_time) {
        scheduleError.value = 'All fields are required.'
        return
    }
    if (newRow.value.start_time >= newRow.value.end_time) {
        scheduleError.value = 'End time must be after start time.'
        return
    }
    savingNew.value = true
    scheduleError.value = null
    try {
        const res = await axios.post(`/shop/employee/${employee.id}/schedule`, newRow.value)
        scheduleList.value.push(res.data)
        newRow.value  = { work_date: '', start_time: '', end_time: '' }
        showNewRow.value = false
    } catch (err: any) {
        scheduleError.value = err.response?.data?.message ?? 'Failed to add schedule.'
    } finally {
        savingNew.value = false
    }
}

function startEdit(s: Schedule) {
    editingId.value = s.id
    editRow.value   = { work_date: s.work_date, start_time: fmt(s.start_time), end_time: fmt(s.end_time) }
}

async function saveEdit(id: number) {
    if (editRow.value.start_time >= editRow.value.end_time) {
        scheduleError.value = 'End time must be after start time.'
        return
    }
    savingEdit.value = true
    scheduleError.value = null
    try {
        const res = await axios.put(`/shop/employee/${employee.id}/schedule/${id}`, editRow.value)
        const idx = scheduleList.value.findIndex(s => s.id === id)
        if (idx !== -1) scheduleList.value[idx] = res.data
        editingId.value = null
    } catch (err: any) {
        scheduleError.value = err.response?.data?.message ?? 'Failed to update schedule.'
    } finally {
        savingEdit.value = false
    }
}

async function deleteSchedule(id: number) {
    deletingId.value = id
    scheduleError.value = null
    try {
        await axios.delete(`/shop/employee/${employee.id}/schedule/${id}`)
        scheduleList.value = scheduleList.value.filter(s => s.id !== id)
    } catch {
        scheduleError.value = 'Failed to delete schedule.'
    } finally {
        deletingId.value = null
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
                        <Input v-model="form.phone" placeholder="09XXXXXXXXX" :class="{ 'border-red-500': errors.phone }" />
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
                        <Input v-model="form.position" placeholder="e.g. Cashier" :class="{ 'border-red-500': errors.position }" />
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
                            <Input v-model="form.branch_name" placeholder="e.g. Main Branch" :class="{ 'border-red-500': errors.branch_name }" />
                        </template>
                        <p v-if="errors.branch_name" class="text-xs text-red-500">{{ errors.branch_name }}</p>
                    </div>

                    <!-- Hire Date — restricted for staff -->
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Hire Date <span class="text-red-500">*</span>
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div v-if="isStaff" class="flex h-9 w-full rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed select-none">
                            {{ form.hire_date }}
                        </div>
                        <Input v-else v-model="form.hire_date" type="date" :class="{ 'border-red-500': errors.hire_date }" />
                        <p v-if="errors.hire_date" class="text-xs text-red-500">{{ errors.hire_date }}</p>
                    </div>

                    <!-- Salary — restricted for staff -->
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Salary (₱)
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div v-if="isStaff" class="flex h-9 w-full rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground cursor-not-allowed select-none">
                            {{ form.salary ? `₱${Number(form.salary).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—' }}
                        </div>
                        <Input v-else v-model="form.salary" type="number" min="0" step="0.01" :class="{ 'border-red-500': errors.salary }" />
                        <p v-if="errors.salary" class="text-xs text-red-500">{{ errors.salary }}</p>
                    </div>

                    <!-- Status — restricted for staff -->
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">
                            Status <span class="text-red-500">*</span>
                            <span v-if="isStaff" class="ml-1 text-xs text-amber-500">(view only)</span>
                        </label>
                        <div
                            v-if="isStaff"
                            class="flex h-9 w-full items-center rounded-md border border-input bg-muted/50 px-3 py-2 text-sm cursor-not-allowed select-none"
                        >
                            <span
                                class="px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                                :class="form.status === 'Active' ? 'bg-green-500' : 'bg-red-500'"
                            >
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
                    <p class="text-xs text-muted-foreground mt-1">Select a new province below to update, or just edit the street field.</p>
                </div>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Province</label>
                        <Select v-model="selectedProvince" :disabled="loadingProvinces">
                            <SelectTrigger>
                                <SelectValue>
                                    <span v-if="loadingProvinces" class="flex items-center gap-1.5 text-muted-foreground"><Loader2 class="h-3 w-3 animate-spin" /> Loading...</span>
                                    <span v-else-if="!selectedProvince" class="text-muted-foreground">Select province</span>
                                    <span v-else>{{ provinces.find(p => p.code === selectedProvince)?.name }}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">City / Municipality</label>
                        <Select v-model="selectedCity" :disabled="!selectedProvince || loadingCities">
                            <SelectTrigger>
                                <SelectValue>
                                    <span v-if="loadingCities" class="flex items-center gap-1.5 text-muted-foreground"><Loader2 class="h-3 w-3 animate-spin" /> Loading...</span>
                                    <span v-else-if="!selectedCity" class="text-muted-foreground">Select city/municipality</span>
                                    <span v-else>{{ cities.find(c => c.code === selectedCity)?.name }}</span>
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
                                    <span v-if="loadingBarangays" class="flex items-center gap-1.5 text-muted-foreground"><Loader2 class="h-3 w-3 animate-spin" /> Loading...</span>
                                    <span v-else-if="!selectedBarangay" class="text-muted-foreground">Select barangay</span>
                                    <span v-else>{{ barangays.find(b => b.code === selectedBarangay)?.name }}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem v-for="b in barangays" :key="b.code" :value="b.code">{{ b.name }}</SelectItem>
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

            <!-- ── Schedule (owner only) ──────────────────────────────────────── -->
            <div v-if="isOwner">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Schedule</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Manage work shifts for this employee.</p>
                    </div>
                    <Button
                        v-if="!showNewRow"
                        size="sm"
                        variant="outline"
                        class="gap-1.5"
                        @click="showNewRow = true; scheduleError = null"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Add Shift
                    </Button>
                </div>

                <div
                    v-if="scheduleError"
                    class="mb-3 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700"
                >
                    <span>{{ scheduleError }}</span>
                    <button @click="scheduleError = null">
                        <X class="h-3.5 w-3.5 ml-3 text-red-400 hover:text-red-600" />
                    </button>
                </div>

                <div class="rounded-xl border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                <th class="text-left px-4 py-3 font-medium w-2/5">Work Date</th>
                                <th class="text-left px-4 py-3 font-medium">Start</th>
                                <th class="text-left px-4 py-3 font-medium">End</th>
                                <th class="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="showNewRow" class="border-b bg-blue-50/60">
                                <td class="px-3 py-2">
                                    <Input v-model="newRow.work_date" type="date" class="h-8 text-sm" />
                                </td>
                                <td class="px-3 py-2">
                                    <Input v-model="newRow.start_time" type="time" class="h-8 text-sm" />
                                </td>
                                <td class="px-3 py-2">
                                    <Input v-model="newRow.end_time" type="time" class="h-8 text-sm" />
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button size="sm" class="h-7 w-7 p-0 bg-green-500 hover:bg-green-600 text-white" :disabled="savingNew" @click="addSchedule">
                                            <Loader2 v-if="savingNew" class="h-3 w-3 animate-spin" />
                                            <Check v-else class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="ghost" class="h-7 w-7 p-0" @click="showNewRow = false; scheduleError = null">
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <template v-for="s in scheduleList" :key="s.id">
                                <tr v-if="editingId !== s.id" class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 font-medium">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                            {{ fmtDate(s.work_date) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ fmt(s.start_time) }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ fmt(s.end_time) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button size="sm" variant="ghost" class="h-7 w-7 p-0 text-blue-500 hover:text-blue-700 hover:bg-blue-50" @click="startEdit(s)">
                                                <Pencil class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="ghost" class="h-7 w-7 p-0 text-red-400 hover:text-red-600 hover:bg-red-50" :disabled="deletingId === s.id" @click="deleteSchedule(s.id)">
                                                <Loader2 v-if="deletingId === s.id" class="h-3.5 w-3.5 animate-spin" />
                                                <Trash2 v-else class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-else class="border-b last:border-0 bg-amber-50/60">
                                    <td class="px-3 py-2">
                                        <Input v-model="editRow.work_date" type="date" class="h-8 text-sm" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input v-model="editRow.start_time" type="time" class="h-8 text-sm" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input v-model="editRow.end_time" type="time" class="h-8 text-sm" />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button size="sm" class="h-7 w-7 p-0 bg-green-500 hover:bg-green-600 text-white" :disabled="savingEdit" @click="saveEdit(s.id)">
                                                <Loader2 v-if="savingEdit" class="h-3 w-3 animate-spin" />
                                                <Check v-else class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="ghost" class="h-7 w-7 p-0" @click="editingId = null">
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <tr v-if="scheduleList.length === 0 && !showNewRow">
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-muted-foreground">
                                    <Calendar class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                    No schedules yet. Click "Add Shift" to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Actions ─────────────────────────────────────────────────────── -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button type="button" variant="outline" :disabled="isSubmitting" @click="router.visit(`${baseRoute}/employee`)">
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
