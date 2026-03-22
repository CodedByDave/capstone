<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Truck, Plus, Pencil, Trash2, Loader2 } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface Supplier {
    id: number
    name: string
    contact_person: string | null
    email: string | null
    phone: string | null
    address: string | null
    status: 'active' | 'inactive'
    notes: string | null
    inventory_count?: number
}

interface Paginator {
    data: Supplier[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{ suppliers: Paginator }>()

const { isOwner, can } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/supplier' : '/staff/supplier')
const inventoryBase = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Suppliers', href: base.value },
]

const supplierList = ref([...props.suppliers.data])
const deletingId   = ref<number | null>(null)

async function deleteSupplier(id: number) {
    deletingId.value = id
    try {
        await axios.delete(`${base.value}/${id}`)
        supplierList.value = supplierList.value.filter(s => s.id !== id)
        toast.success('Supplier archived.', { autoClose: 3000 })
    } catch {
        toast.error('Failed to archive supplier.', { autoClose: 4000 })
    } finally {
        deletingId.value = null
    }
}
</script>

<template>
    <Head title="Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Suppliers">
        <div class="px-6 space-y-6">

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Truck class="h-4 w-4 text-muted-foreground" /> Supplier List
                        </CardTitle>
                        <div class="flex gap-2">
                            <Button v-if="isOwner || can('Inventory Management', 'archive')"
                                size="sm" variant="outline"
                                class="bg-red-500 text-white hover:bg-red-700 hover:text-white"
                                @click="router.visit(`${base}/archive`)">
                                <Trash2 class="h-4 w-4 mr-1.5" /> Archive
                            </Button>
                            <Button v-if="isOwner || can('Inventory Management', 'create')"
                                size="sm"
                                @click="router.visit(`${base}/create`)">
                                <Plus class="h-4 w-4 mr-1.5" /> Add Supplier
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Supplier</th>
                                    <th class="text-left px-4 py-3 font-medium">Contact Person</th>
                                    <th class="text-left px-4 py-3 font-medium">Phone</th>
                                    <th class="text-left px-4 py-3 font-medium">Email Address</th>
                                    <th class="text-left px-4 py-3 font-medium">Items</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in supplierList" :key="s.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ s.name }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.contact_person" class="text-xs text-muted-foreground">
                                            {{ s.contact_person }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.phone" class="text-xs text-muted-foreground">
                                            {{ s.phone }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.email" class="text-xs text-muted-foreground">
                                            {{ s.email }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ s.inventory_count ?? 0 }} items
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="s.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ s.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button v-if="isOwner || can('Inventory Management', 'update')"
                                                size="icon" variant="ghost"
                                                @click="router.visit(`${base}/${s.id}/edit`)">
                                                <Pencil class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button v-if="isOwner || can('Inventory Management', 'archive')"
                                                size="icon" variant="ghost"
                                                :disabled="deletingId === s.id"
                                                @click="deleteSupplier(s.id)">
                                                <Loader2 v-if="deletingId === s.id" class="h-4 w-4 animate-spin" />
                                                <Trash2 v-else class="h-4 w-4 text-red-400" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="supplierList.length === 0">
                                    <td colspan="7" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Truck class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No suppliers yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="suppliers.last_page > 1" class="flex justify-end gap-1 pt-4">
                        <Button v-for="link in suppliers.links" :key="link.label" size="sm"
                            :variant="link.active ? 'default' : 'outline'" :disabled="!link.url"
                            class="h-7 min-w-7 text-xs"
                            @click="link.url && router.visit(link.url)"
                            v-html="link.label" />
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
