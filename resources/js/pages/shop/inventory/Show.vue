<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Pencil, AlertTriangle, ArrowUpDown, Loader2, X } from 'lucide-vue-next'
import axios from 'axios'
import { usePermissions } from '@/composables/usePermissions'

interface Movement {
    id: number
    type: string
    quantity: number
    quantity_before: number
    quantity_after: number
    reference_number: string | null
    notes: string | null
    created_at: string
    user: { name: string } | null
}

interface InventoryItem {
    id: number
    name: string
    sku: string
    description: string | null
    unit: string
    quantity: number
    min_stock: number
    max_stock: number | null
    unit_price: string | null
    selling_price: string | null
    status: 'active' | 'inactive'
    category: { id: number; name: string } | null
    supplier: { id: number; name: string; phone: string | null } | null
    movements: Movement[]
}

const { inventory } = defineProps<{ inventory: InventoryItem }>()

const { isOwner, can } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory',    href: base.value },
    { title: inventory.name, href: `${base.value}/${inventory.id}` },
]

// ─── Stock Adjust ─────────────────────────────────────────────────────────────

const showAdjust  = ref(false)
const adjustError = ref<string | null>(null)
const adjusting   = ref(false)

const adjustForm = ref({
    type:             'restock',
    quantity:         1,
    reference_number: '',
    notes:            '',
})

async function submitAdjust() {
    if (!adjustForm.value.quantity || adjustForm.value.quantity === 0) {
        adjustError.value = 'Quantity cannot be zero.'
        return
    }

    adjusting.value   = true
    adjustError.value = null

    try {
        await axios.post(`${base.value}/${inventory.id}/adjust`, adjustForm.value)
        router.reload({ only: ['inventory'] })
        showAdjust.value = false
        adjustForm.value = { type: 'restock', quantity: 1, reference_number: '', notes: '' }
    } catch (err: any) {
        adjustError.value = err.response?.data?.errors?.quantity?.[0]
            ?? err.response?.data?.message
            ?? 'Failed to adjust stock.'
    } finally {
        adjusting.value = false
    }
}

function formatPrice(price: string | null) {
    if (!price) return '—'
    return `₱${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDateTime(dt: string) {
    return new Date(dt).toLocaleString('en-PH', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const movementTypeStyle: Record<string, string> = {
    restock:    'bg-green-100 text-green-700',
    usage:      'bg-blue-100 text-blue-700',
    adjustment: 'bg-purple-100 text-purple-700',
    return:     'bg-teal-100 text-teal-700',
    damage:     'bg-red-100 text-red-700',
}
</script>

<template>
    <Head :title="inventory.name" />
    <ShopLayout :breadcrumbs="breadcrumbs" :title="inventory.name">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
                <div class="flex gap-2">
                    <Button v-if="isOwner || can('Inventory Management', 'update')"
                        variant="outline" @click="showAdjust = !showAdjust">
                        <ArrowUpDown class="h-4 w-4 mr-2" /> Adjust Stock
                    </Button>
                    <Button v-if="isOwner || can('Inventory Management', 'update')"
                        @click="router.visit(`${base}/${inventory.id}/edit`)">
                        <Pencil class="h-4 w-4 mr-2" /> Edit
                    </Button>
                </div>
            </div>

            <!-- Low stock warning -->
            <div v-if="inventory.quantity <= inventory.min_stock"
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <AlertTriangle class="h-4 w-4 shrink-0" />
                <span>
                    Stock is {{ inventory.quantity === 0 ? 'out' : 'low' }}.
                    Current: <strong>{{ inventory.quantity }}</strong> {{ inventory.unit }}
                    (min: {{ inventory.min_stock }})
                </span>
            </div>

            <!-- Adjust stock panel -->
            <Card v-if="showAdjust" class="border-blue-200 bg-blue-50/30">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-sm flex items-center gap-2">
                            <ArrowUpDown class="h-4 w-4" /> Adjust Stock
                        </CardTitle>
                        <Button size="icon" variant="ghost" class="h-7 w-7" @click="showAdjust = false">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="adjustError"
                        class="mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">
                        {{ adjustError }}
                    </div>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Type</label>
                            <Select v-model="adjustForm.type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="restock">Restock</SelectItem>
                                    <SelectItem value="usage">Usage</SelectItem>
                                    <SelectItem value="adjustment">Adjustment</SelectItem>
                                    <SelectItem value="return">Return</SelectItem>
                                    <SelectItem value="damage">Damage</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="col-span-12 sm:col-span-2 space-y-1">
                            <label class="text-sm font-medium">Quantity</label>
                            <Input v-model="adjustForm.quantity" type="number" min="1" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Reference No. <span class="text-muted-foreground text-xs">(optional)</span></label>
                            <Input v-model="adjustForm.reference_number" placeholder="e.g. PO-001" />
                        </div>
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Notes <span class="text-muted-foreground text-xs">(optional)</span></label>
                            <Input v-model="adjustForm.notes" placeholder="Reason for adjustment..." />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="showAdjust = false">Cancel</Button>
                        <Button :disabled="adjusting" @click="submitAdjust">
                            <Loader2 v-if="adjusting" class="h-4 w-4 mr-2 animate-spin" />
                            {{ adjusting ? 'Saving...' : 'Save Adjustment' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Details -->
                <Card class="md:col-span-2">
                    <CardHeader><CardTitle>Item Details</CardTitle></CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Name</p>
                                <p class="font-medium">{{ inventory.name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">SKU</p>
                                <p class="font-mono">{{ inventory.sku }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Unit</p>
                                <p>{{ inventory.unit }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Status</p>
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium"
                                    :class="inventory.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                    {{ inventory.status }}
                                </span>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Category</p>
                                <p>{{ inventory.category?.name ?? '—' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Supplier</p>
                                <p>{{ inventory.supplier?.name ?? '—' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Unit Price</p>
                                <p>{{ formatPrice(inventory.unit_price) }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Selling Price</p>
                                <p>{{ formatPrice(inventory.selling_price) }}</p>
                            </div>
                            <div v-if="inventory.description" class="col-span-2 space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Description</p>
                                <p class="text-muted-foreground">{{ inventory.description }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Stock summary -->
                <div class="space-y-4">
                    <Card>
                        <CardContent class="pt-5 text-center">
                            <p class="text-xs font-semibold uppercase text-muted-foreground tracking-widest">Current Stock</p>
                            <p class="text-5xl font-bold mt-2"
                                :class="inventory.quantity === 0 ? 'text-red-600' : inventory.quantity <= inventory.min_stock ? 'text-amber-600' : 'text-green-600'">
                                {{ inventory.quantity }}
                            </p>
                            <p class="text-sm text-muted-foreground mt-1">{{ inventory.unit }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-5 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Min Stock</span>
                                <span class="font-medium">{{ inventory.min_stock }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Max Stock</span>
                                <span class="font-medium">{{ inventory.max_stock ?? '—' }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Movement history -->
            <Card>
                <CardHeader><CardTitle>Stock Movement History</CardTitle></CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Type</th>
                                    <th class="text-left px-4 py-3 font-medium">Quantity</th>
                                    <th class="text-left px-4 py-3 font-medium">Before</th>
                                    <th class="text-left px-4 py-3 font-medium">After</th>
                                    <th class="text-left px-4 py-3 font-medium">Reference</th>
                                    <th class="text-left px-4 py-3 font-medium">By</th>
                                    <th class="text-left px-4 py-3 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in inventory.movements" :key="m.id" class="border-b last:border-0">
                                    <td class="px-4 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="movementTypeStyle[m.type] ?? 'bg-gray-100 text-gray-600'">
                                            {{ m.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium"
                                        :class="m.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ m.quantity_before }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ m.quantity_after }}</td>
                                    <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ m.reference_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">{{ m.user?.name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">{{ formatDateTime(m.created_at) }}</td>
                                </tr>
                                <tr v-if="inventory.movements.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-muted-foreground text-sm">
                                        No stock movements recorded yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
