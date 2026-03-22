<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { usePermissions } from '@/composables/usePermissions'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Layers, ArchiveRestore, ArrowLeft, Loader2, Inbox } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface Category {
    id: number
    name: string
    description: string | null
    deleted_at: string
}

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const props = defineProps<{ archived: Category[] }>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/shop/inventory' },
    { title: 'Categories', href: '/shop/inventory/category' },
    { title: 'Archive', href: '/shop/inventory/category/archive' },
]

const archivedList = ref<Category[]>([...props.archived])
const restoringId = ref<number | null>(null)

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

async function restoreCategory(id: number) {
    restoringId.value = id
    try {
        await axios.post(`${base.value}/category/${id}/restore`)
        archivedList.value = archivedList.value.filter(c => c.id !== id)
        toast.success('Category restored successfully.', { autoClose: 3000 })
    } catch {
        toast.error('Failed to restore category.', { autoClose: 4000 })
    } finally {
        restoringId.value = null
    }
}
</script>

<template>

    <Head title="Archived Categories" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Categories">
        <div class="px-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Categories</h2>
                    <p class="text-sm text-muted-foreground">Restore categories to make them available again.</p>
                </div>
                <Button variant="outline" @click="router.visit(`${base}/category`)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back to Categories
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Layers class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span class="text-xs font-normal bg-muted px-2 py-0.5 rounded-full">
                            {{ archivedList.length }}
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Name</th>
                                    <th class="text-left px-4 py-3 font-medium">Description</th>
                                    <th class="text-left px-4 py-3 font-medium">Archived On</th>
                                    <th class="text-right px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cat in archivedList" :key="cat.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors opacity-70 hover:opacity-100">
                                    <td class="px-4 py-3 font-medium text-muted-foreground line-through">
                                        {{ cat.name }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">
                                        {{ cat.description ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ formatDate(cat.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Button size="sm" variant="outline" :disabled="restoringId === cat.id"
                                            class="h-7 gap-1.5 text-xs" @click="restoreCategory(cat.id)">
                                            <Loader2 v-if="restoringId === cat.id" class="h-3 w-3 animate-spin" />
                                            <ArchiveRestore v-else class="h-3 w-3" />
                                            Restore
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="archivedList.length === 0">
                                    <td colspan="4" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Inbox class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No archived categories.
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
