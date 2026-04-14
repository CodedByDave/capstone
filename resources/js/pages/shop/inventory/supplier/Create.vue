<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, watch, onMounted, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'

interface PsgcItem { code: string; name: string }

const page = usePage()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: `${baseRoute.value}/inventory` },
    { title: 'Suppliers', href: `${baseRoute.value}/supplier` },
    { title: 'Add Supplier', href: `${baseRoute.value}/supplier/create` },
]

const BASE = 'https://psgc.cloud/api'

const provinces  = ref<PsgcItem[]>([])
const cities     = ref<PsgcItem[]>([])
const barangays  = ref<PsgcItem[]>([])

const loadingProvinces  = ref(false)
const loadingCities     = ref(false)
const loadingBarangays  = ref(false)

const selectedProvince  = ref('')
const selectedCity      = ref('')
const selectedBarangay  = ref('')
const streetInput       = ref('')

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
    return [
        streetInput.value.trim(),
        barangays.value.find(b => b.code === selectedBarangay.value)?.name ?? '',
        cities.value.find(c => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find(p => p.code === selectedProvince.value)?.name ?? '',
    ].filter(Boolean).join(', ')
})

const form = useForm({
    name:           '',
    contact_person: '',
    email:          '',
    phone:          '',
    address:        '',
    status:         'active',
    notes:          '',
})

function submit() {
    form.address = fullAddress.value || form.address
    form.post(`${baseRoute.value}/supplier`, {
        onSuccess: () => form.reset(),
    })
}
</script>


<template>
    <Head title="Add Supplier" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Add Supplier">
        <div class="px-6 space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Supplier</h2>
                    <p class="text-sm text-muted-foreground">Add a supplier for your inventory items.</p>
                </div>
                <Button variant="outline" @click="router.visit(`${baseRoute}/supplier`)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
            </div>

            <!-- Basic Info -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Supplier Information</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">

                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Supplier Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.name" placeholder="e.g. ABC Trading" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Contact Person</label>
                        <Input v-model="form.contact_person" placeholder="e.g. Juan Dela Cruz" />
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Email</label>
                        <Input v-model="form.email" type="email" placeholder="supplier@email.com" :class="{ 'border-red-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="form.phone" placeholder="09XXXXXXXXX" />
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Status</label>
                        <Select v-model="form.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Notes</label>
                        <Textarea v-model="form.notes" placeholder="Optional notes about this supplier..." rows="2" />
                    </div>

                </div>
            </div>

            <!-- Address via PSGC -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Address</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Province</label>
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

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">City / Municipality</label>
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

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Barangay</label>
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

                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Street / Block No.</label>
                        <Input v-model="streetInput" placeholder="e.g. 123 Rizal St." />
                    </div>

                </div>

                <p v-if="fullAddress" class="mt-3 text-xs text-muted-foreground">
                    <span class="font-medium">Full address:</span> {{ fullAddress }}
                </p>
                <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button variant="outline" :disabled="form.processing" @click="router.visit(`${baseRoute}/supplier`)">Cancel</Button>
                <Button :disabled="form.processing" @click="submit">
                    <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                    {{ form.processing ? 'Saving...' : 'Add Supplier' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
