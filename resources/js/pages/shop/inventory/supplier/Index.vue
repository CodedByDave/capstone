<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Truck, Plus, Pencil, Trash2, ArrowLeft, Loader2, Phone, Mail, MapPin } from 'lucide-vue-next'

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

const { suppliers } = defineProps<{ suppliers: Paginator }>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/shop/inventory' },
    { title: 'Suppliers', href: '/shop/supplier' },
]
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
                            <Button size="sm" variant="outline" @click="router.visit('/shop/supplier/archive')" class="bg-red-500 text-white hover:bg-red-700 hover:text-white">
                                <Trash2 class="h-4 w-4 mr-1.5" /> Archive
                            </Button>
                            <Button size="sm" @click="router.visit('/shop/supplier/create')">
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
                                <tr v-for="s in suppliers.data" :key="s.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ s.name }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.contact_person" class="text-xs text-muted-foreground">{{
                                            s.contact_person }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.phone" class="flex items-center gap-1 text-xs text-muted-foreground">
                                            {{ s.phone }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p v-if="s.email" class="flex items-center gap-1 text-xs text-muted-foreground">
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
                                            <Button size="icon" variant="ghost"
                                                @click="router.visit(`/shop/supplier/${s.id}/edit`)">
                                                <Pencil class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost"
                                                @click="router.delete(`/shop/supplier/${s.id}`)">
                                                <Trash2 class="h-4 w-4 text-red-400" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="suppliers.data.length === 0">
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-muted-foreground">
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
                            class="h-7 min-w-7 text-xs" @click="link.url && router.visit(link.url)"
                            v-html="link.label" />
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
