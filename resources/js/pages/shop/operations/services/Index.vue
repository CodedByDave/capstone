<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, watch } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import {
    Scissors, Plus, Pencil, Trash2, Search,
    ToggleLeft, ToggleRight, Package, Weight,
    CheckCircle, X, Archive, RotateCcw
} from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// ─── Types ─────────────────────────────────────────

interface ShopService {
    id: number
    service_name: string
    description: string | null
    is_active: boolean
    pricing_model: 'per_kg' | 'per_bundle'
    price_per_kg: string | null
    bundle_weight_kg: string | null
    bundle_price: string | null
    estimated_hours: number | null
}

// ─── Props ─────────────────────────────────────────

const props = defineProps<{
    services: ShopService[]
    pricingModels: string[]
    showArchived: boolean
}>()

const page = usePage<AppPageProps>()

watch(
    () => page.props.toast as any,
    (t) => {
        if (!t) return
        if (t.type === 'success') toast.success(t.message)
        if (t.type === 'error')   toast.error(t.message)
        if (t.type === 'warning') toast.warning(t.message)
        if (t.type === 'info')    toast.info(t.message)
    },
    { immediate: true }
)

// ─── Base ──────────────────────────────────────────

const base = '/shop/operations/services'

// ─── Search ────────────────────────────────────────

const search = ref('')

const filtered = computed(() => {
    if (!search.value) return props.services
    const q = search.value.toLowerCase()
    return props.services.filter(s =>
        s.service_name.toLowerCase().includes(q) ||
        s.description?.toLowerCase().includes(q)
    )
})

// ─── KPIs ──────────────────────────────────────────

const totalServices   = computed(() => props.services.length)
const activeServices  = computed(() => props.services.filter(s => s.is_active).length)
const perKgServices   = computed(() => props.services.filter(s => s.pricing_model === 'per_kg').length)
const perBundleServices = computed(() => props.services.filter(s => s.pricing_model === 'per_bundle').length)

// ─── Helpers ───────────────────────────────────────

const formatCurrency = (v: string | number | null) =>
    v !== null ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—'

const formatPrice = (s: ShopService) =>
    s.pricing_model === 'per_kg'
        ? `${formatCurrency(s.price_per_kg)}/kg`
        : `${formatCurrency(s.bundle_price)} / ${s.bundle_weight_kg}kg bundle`

// ─── Toggle View ───────────────────────────────────

const toggleArchived = () => {
    router.get(base, { archived: props.showArchived ? undefined : true }, {
        preserveState: false,
        replace: true,
    })
}

// ─── Delete Dialog ─────────────────────────────────

const serviceToDelete = ref<ShopService | null>(null)
const showDeleteDialog = ref(false)

const confirmDelete = (service: ShopService) => {
    serviceToDelete.value = service
    showDeleteDialog.value = true
}

const handleDelete = () => {
    if (!serviceToDelete.value) return
    router.delete(`${base}/${serviceToDelete.value.id}`)
    showDeleteDialog.value = false
    serviceToDelete.value = null
}

// ─── Restore ───────────────────────────────────────

const restoreService = (service: ShopService) => {
    router.patch(`${base}/${service.id}/restore`)
}

const restoreAll = () => {
    router.post(`${base}/restore-all`)
}

// ─── Toggle Active ─────────────────────────────────

const toggleActive = (service: ShopService) => {
    router.patch(`${base}/${service.id}/toggle`)
}
</script>

<template>
    <Head :title="showArchived ? 'Archived Services' : 'Services & Pricing'" />

    <ShopLayout :title="showArchived ? 'Archived Services' : 'Services & Pricing'">
        <div class="px-6 space-y-6">

            <!-- ── KPI Cards (active view only) ──────────── -->
            <div v-if="!showArchived" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Total Services</p>
                            <Scissors class="h-4 w-4 text-blue-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ totalServices }}</p>
                        <p class="text-xs text-muted-foreground mt-1">All pricing types</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Active</p>
                            <CheckCircle class="h-4 w-4 text-emerald-500" />
                        </div>
                        <p class="text-2xl font-bold text-emerald-600">{{ activeServices }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ totalServices - activeServices }} inactive</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Per KG</p>
                            <Weight class="h-4 w-4 text-blue-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ perKgServices }}</p>
                        <p class="text-xs text-muted-foreground mt-1">Weight-based pricing</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Per Bundle</p>
                            <Package class="h-4 w-4 text-violet-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ perBundleServices }}</p>
                        <p class="text-xs text-muted-foreground mt-1">Bundle-based pricing</p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Archived Banner ───────────────────────── -->
            <div v-if="showArchived"
                class="flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3">
                <Archive class="h-5 w-5 text-amber-600 shrink-0" />
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">Viewing Archived Services</p>
                    <p class="text-xs text-amber-600">These services have been soft-deleted. You can restore them individually or all at once.</p>
                </div>
                <Button
                    v-if="services.length > 0"
                    size="sm"
                    variant="outline"
                    class="border-amber-300 text-amber-700 hover:bg-amber-100 gap-1.5"
                    @click="restoreAll"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Restore All
                </Button>
            </div>

            <!-- ── Table Card ─────────────────────────────── -->
            <Card>
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <CardTitle>
                            {{ showArchived ? 'Archived Services' : 'Services List' }}
                        </CardTitle>
                        <div class="flex gap-2">
                            <!-- Toggle archive view -->
                            <Button
                                variant="outline"
                                size="sm"
                                class="gap-1.5"
                                :class="showArchived ? 'border-amber-400 text-amber-700 bg-amber-50' : ''"
                                @click="toggleArchived"
                            >
                                <Archive class="h-4 w-4" />
                                {{ showArchived ? 'Back to Active' : 'View Archives' }}
                            </Button>

                            <!-- Only show New Service on active view -->
                            <Button v-if="!showArchived" @click="router.visit(`${base}/create`)">
                                <Plus class="h-4 w-4 mr-1" /> New Service
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">

                    <!-- Search -->
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" class="pl-8"
                                :placeholder="showArchived ? 'Search archived services…' : 'Search services…'" />
                        </div>
                        <Button v-if="search" variant="ghost" size="sm" class="h-9 gap-1" @click="search = ''">
                            <X class="h-3.5 w-3.5" /> Clear
                        </Button>
                    </div>

                    <!-- Table -->
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/40 text-xs uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-3 text-left">Service Name</th>
                                    <th class="px-4 py-3 text-left">Pricing Model</th>
                                    <th class="px-4 py-3 text-left">Price</th>
                                    <th class="px-4 py-3 text-left">Est. Turnaround</th>
                                    <th v-if="!showArchived" class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="service in filtered"
                                    :key="service.id"
                                    class="border-b last:border-0 hover:bg-muted/30 transition-colors"
                                    :class="{ 'opacity-60': !service.is_active && !showArchived }"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ service.service_name }}</p>
                                        <p v-if="service.description"
                                            class="text-xs text-muted-foreground truncate max-w-[200px]">
                                            {{ service.description }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="service.pricing_model === 'per_kg'
                                                ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
                                                : 'bg-violet-50 text-violet-700 ring-1 ring-violet-200'"
                                        >
                                            <Weight v-if="service.pricing_model === 'per_kg'" class="h-3 w-3" />
                                            <Package v-else class="h-3 w-3" />
                                            {{ service.pricing_model === 'per_kg' ? 'Per KG' : 'Per Bundle' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 font-medium">{{ formatPrice(service) }}</td>

                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ service.estimated_hours ? `${service.estimated_hours}h` : '—' }}
                                    </td>

                                    <!-- Status col — active view only -->
                                    <td v-if="!showArchived" class="px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="service.is_active
                                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                                : 'bg-gray-100 text-gray-500 ring-1 ring-gray-200'"
                                        >
                                            {{ service.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-0.5">

                                            <!-- Active view actions -->
                                            <template v-if="!showArchived">
                                                <Button size="icon" variant="ghost"
                                                    :title="service.is_active ? 'Deactivate' : 'Activate'"
                                                    @click="toggleActive(service)">
                                                    <ToggleRight v-if="service.is_active" class="h-4 w-4 text-emerald-500" />
                                                    <ToggleLeft v-else class="h-4 w-4 text-gray-400" />
                                                </Button>
                                                <Button size="icon" variant="ghost"
                                                    @click="router.visit(`${base}/${service.id}/edit`)">
                                                    <Pencil class="h-4 w-4 text-green-500" />
                                                </Button>
                                                <Button size="icon" variant="ghost" @click="confirmDelete(service)">
                                                    <Trash2 class="h-4 w-4 text-red-500" />
                                                </Button>
                                            </template>

                                            <!-- Archived view actions -->
                                            <template v-else>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    class="gap-1.5 text-emerald-700 border-emerald-300 hover:bg-emerald-50"
                                                    @click="restoreService(service)"
                                                >
                                                    <RotateCcw class="h-3.5 w-3.5" /> Restore
                                                </Button>
                                            </template>

                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="filtered.length === 0">
                                    <td :colspan="showArchived ? 5 : 6" class="text-center py-12 text-muted-foreground">
                                        <component
                                            :is="showArchived ? Archive : Scissors"
                                            class="h-10 w-10 mx-auto mb-2 opacity-30"
                                        />
                                        <p>{{ showArchived ? 'No archived services' : 'No services found' }}</p>
                                        <p v-if="search" class="text-xs mt-1">Try adjusting your search</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </CardContent>
            </Card>

        </div>

        <!-- ── Delete Alert Dialog ────────────────────── -->
        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Service</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <span class="font-semibold text-foreground">{{ serviceToDelete?.service_name }}</span>?
                        It will be moved to the archive and can be restored later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteDialog = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-red-600 hover:bg-red-700 text-white"
                        @click="handleDelete"
                    >
                        Archive Service
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
