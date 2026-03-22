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

const { categories, suppliers } = defineProps<{
    categories: Category[]
    suppliers:  Supplier[]
}>()

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory',  href: base.value },
    { title: 'Add Item',   href: `${base.value}/create` },
]

const form = useForm({
    name:                    '',
    sku:                     '',
    description:             '',
    unit:                    'pcs',
    quantity:                0,
    min_stock:               5,
    max_stock:               '',
    unit_price:              '',
    selling_price:           '',
    status:                  'active',
    inventory_categories_id: '',
    supplier_id:             '',
})

function submit() {
    form.post(base.value, {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Add Inventory Item" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Add Inventory Item">
        <div class="px-6 space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Inventory Item</h2>
                    <p class="text-sm text-muted-foreground">Fill in the details to add a new item.</p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
            </div>

            <!-- Basic Info -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Basic Information</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Item Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.name" placeholder="e.g. Detergent Powder" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">SKU <span class="text-red-500">*</span></label>
                        <Input v-model="form.sku" placeholder="e.g. DET-001" class="font-mono" :class="{ 'border-red-500': form.errors.sku }" />
                        <p v-if="form.errors.sku" class="text-xs text-red-500">{{ form.errors.sku }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Unit <span class="text-red-500">*</span></label>
                        <Select v-model="form.unit">
                            <SelectTrigger :class="{ 'border-red-500': form.errors.unit }"><SelectValue placeholder="Select unit" /></SelectTrigger>
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
                        <p v-if="form.errors.unit" class="text-xs text-red-500">{{ form.errors.unit }}</p>
                    </div>
                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Textarea v-model="form.description" placeholder="Optional item description..." rows="2" />
                        <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Stock -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Stock</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Initial Quantity <span class="text-red-500">*</span></label>
                        <Input v-model="form.quantity" type="number" min="0" :class="{ 'border-red-500': form.errors.quantity }" />
                        <p v-if="form.errors.quantity" class="text-xs text-red-500">{{ form.errors.quantity }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Min Stock <span class="text-red-500">*</span></label>
                        <Input v-model="form.min_stock" type="number" min="0" :class="{ 'border-red-500': form.errors.min_stock }" />
                        <p class="text-xs text-muted-foreground mt-0.5">Alert triggers at or below this.</p>
                        <p v-if="form.errors.min_stock" class="text-xs text-red-500">{{ form.errors.min_stock }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-3 space-y-1">
                        <label class="text-sm font-medium">Max Stock <span class="text-muted-foreground text-xs">(optional)</span></label>
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
                        <label class="text-sm font-medium">Unit Price (₱) <span class="text-muted-foreground text-xs">(cost from supplier)</span></label>
                        <Input v-model="form.unit_price" type="number" min="0" step="0.01" placeholder="0.00" :class="{ 'border-red-500': form.errors.unit_price }" />
                        <p v-if="form.errors.unit_price" class="text-xs text-red-500">{{ form.errors.unit_price }}</p>
                    </div>
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Selling Price (₱) <span class="text-muted-foreground text-xs">(charged to customer)</span></label>
                        <Input v-model="form.selling_price" type="number" min="0" step="0.01" placeholder="0.00" :class="{ 'border-red-500': form.errors.selling_price }" />
                        <p v-if="form.errors.selling_price" class="text-xs text-red-500">{{ form.errors.selling_price }}</p>
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
                                <SelectItem value="none">No Category</SelectItem>
                                <SelectItem v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Supplier</label>
                        <Select v-model="form.supplier_id">
                            <SelectTrigger><SelectValue placeholder="Select supplier" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">No Supplier</SelectItem>
                                <SelectItem v-for="s in suppliers" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button variant="outline" :disabled="form.processing" @click="router.visit(base)">Cancel</Button>
                <Button :disabled="form.processing" @click="submit">
                    <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                    {{ form.processing ? 'Saving...' : 'Add Item' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
