<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Truck, ArchiveRestore, ArrowLeft, Loader2, Phone, Mail, Inbox } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { usePermissions } from '@/composables/usePermissions'
import axios from 'axios'

interface Supplier {
    id: number
    name: string
    contact_person: string | null
    email: string | null
    phone: string | null
    address: string | null
    status: string
    inventory_count?: number
    deleted_at: string
}

interface Paginator {
    data: Supplier[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{ archived: Paginator }>()

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/supplier' : '/staff/supplier')
const inventoryBase = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Suppliers', href: base.value },
    { title: 'Archive',   href: `${base.value}/archive` },
]

const restoringId  = ref<number | null>(null)
const archivedList = ref([...props.archived.data])

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

async function restoreSupplier(id: number) {
    restoringId.value = id
    try {
        await axios.post(`${base.value}/${id}/restore`)
        archivedList.value = archivedList.value.filter(s => s.id !== id)
        toast.success('Supplier restored successfully.', { autoClose: 3000 })
    } catch {
        toast.error('Failed to restore supplier.', { autoClose: 4000 })
    } finally {
        restoringId.value = null
    }
}
</script>

<template>
    <Head title="Archived Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Suppliers">
        <div class="px-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Suppliers</h2>
                    <p class="text-sm text-muted-foreground">Restore suppliers to make them available again.</p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back to Suppliers
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Truck class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span class="text-xs font-normal bg-muted px-2 py-0.5 rounded-full">
                            {{ archived.total }}
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Supplier</th>
                                    <th class="text-left px-4 py-3 font-medium">Contact</th>
                                    <th class="text-left px-4 py-3 font-medium">Items</th>
                                    <th class="text-left px-4 py-3 font-medium">Archived On</th>
                                    <th class="text-right px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="s in archivedList" :key="s.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors opacity-70 hover:opacity-100"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium line-through text-muted-foreground">{{ s.name }}</p>
                                        <p v-if="s.contact_person" class="text-xs text-muted-foreground">{{ s.contact_person }}</p>
                                    </td>
                                    <td class="px-4 py-3 space-y-0.5">
                                        <div v-if="s.phone" class="flex items-center gap-1 text-xs text-muted-foreground">
                                            <Phone class="h-3 w-3" /> {{ s.phone }}
                                        </div>
                                        <div v-if="s.email" class="flex items-center gap-1 text-xs text-muted-foreground">
                                            <Mail class="h-3 w-3" /> {{ s.email }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">
                                        {{ s.inventory_count ?? 0 }} items
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ formatDate(s.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            size="sm" variant="outline"
                                            :disabled="restoringId === s.id"
                                            class="h-7 gap-1.5 text-xs"
                                            @click="restoreSupplier(s.id)"
                                        >
                                            <Loader2 v-if="restoringId === s.id" class="h-3 w-3 animate-spin" />
                                            <ArchiveRestore v-else class="h-3 w-3" />
                                            Restore
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="archivedList.length === 0">
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Inbox class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No archived suppliers.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="archived.last_page > 1" class="flex justify-end gap-1 pt-4">
                        <Button
                            v-for="link in archived.links" :key="link.label"
                            size="sm" :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url" class="h-7 min-w-7 text-xs"
                            @click="link.url && router.visit(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
