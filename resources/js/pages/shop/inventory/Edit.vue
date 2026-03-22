<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

interface Category { id: number; name: string }
interface Supplier  { id: number; name: string }
interface InventoryItem {
    id: number; name: string; sku: string; description: string | null
    unit: string; quantity: number; min_stock: number; max_stock: number | null
    unit_price: string | null; selling_price: string | null; status: string
    inventory_categories_id: number | null; supplier_id: number | null
}

const { inventory, categories, suppliers } = defineProps<{
    inventory:  InventoryItem
    categories: Category[]
    suppliers:  Supplier[]
}>()

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory',      href: base.value },
    { title: inventory.name,   href: `${base.value}/${inventory.id}` },
    { title: 'Edit',           href: `${base.value}/${inventory.id}/edit` },
]

const form = useForm({
    name:                    inventory.name,
    sku:                     inventory.sku,
    description:             inventory.description ?? '',
    unit:                    inventory.unit,
    min_stock:               inventory.min_stock,
    max_stock:               inventory.max_stock ?? '',
    unit_price:              inventory.unit_price ?? '',
    selling_price:           inventory.selling_price ?? '',
    status:                  inventory.status,
    inventory_categories_id: inventory.inventory_categories_id ? String(inventory.inventory_categories_id) : '',
    supplier_id:             inventory.supplier_id ? String(inventory.supplier_id) : '',
})

function submit() {
    form.put(`${base.value}/${inventory.id}`)
}
</script>

<template>
    <Head :title="`Edit — ${inventory.name}`" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Inventory Item">
        <div class="px-6 space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Edit Item</h2>
                    <p class="text-sm text-muted-foreground">Update details for <span class="font-medium text-foreground">{{ inventory.name }}</span>.</p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back to inventory
                </Button>
            </div>

            <!-- Basic Info -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Basic Information</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Item Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.name" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">SKU <span class="text-red-500">*</span></label>
                        <Input v-model="form.sku" class="font-mono" :class="{ 'border-red-500': form.errors.sku }" />
                        <p v-if="form.errors.sku" class="text-xs text-red-500">{{ form.errors.sku }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Unit <span class="text-red-500">*</span></label>
                        <Select v-model="form.unit">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pcs">Pieces (pcs)</SelectItem>
                                <SelectItem value="kg">Kilograms (kg)</SelectItem>
                                <SelectItem value="g">Grams (g)</SelectItem>
                                <SelectItem value="liters">Liters (L)</SelectItem>
                                <SelectItem value="ml">Milliliters (ml)</SelectItem>
                                <SelectItem value="bottles">Bottles</SelectItem>
                                <SelectItem value="boxes">Boxes</SelectItem>
                                <SelectItem value="packs">Packs</SelectItem>
                                <SelectItem value="rolls">Rolls</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Textarea v-model="form.description" rows="2" />
                    </div>
                </div>
            </div>

            <!-- Stock thresholds -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Stock Thresholds</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Min Stock <span class="text-red-500">*</span></label>
                        <Input v-model="form.min_stock" type="number" min="0" :class="{ 'border-red-500': form.errors.min_stock }" />
                        <p v-if="form.errors.min_stock" class="text-xs text-red-500">{{ form.errors.min_stock }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Max Stock</label>
                        <Input v-model="form.max_stock" type="number" min="0" placeholder="No limit" :class="{ 'border-red-500': form.errors.max_stock }" />
                        <p v-if="form.errors.max_stock" class="text-xs text-red-500">{{ form.errors.max_stock }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Status <span class="text-red-500">*</span></label>
                        <Select v-model="form.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Pricing</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Unit Price (₱)</label>
                        <Input v-model="form.unit_price" type="number" min="0" step="0.01" placeholder="0.00" />
                    </div>
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Selling Price (₱)</label>
                        <Input v-model="form.selling_price" type="number" min="0" step="0.01" placeholder="0.00" />
                    </div>
                </div>
            </div>

            <!-- Classification -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Classification</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="form.inventory_categories_id">
                            <SelectTrigger><SelectValue placeholder="Select category" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">No Category</SelectItem>
                                <SelectItem v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Supplier</label>
                        <Select v-model="form.supplier_id">
                            <SelectTrigger><SelectValue placeholder="Select supplier" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">No Supplier</SelectItem>
                                <SelectItem v-for="s in suppliers" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button variant="outline" :disabled="form.processing"
                    @click="router.visit(`${base}/${inventory.id}`)">Cancel</Button>
                <Button :disabled="form.processing" @click="submit">
                    <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
