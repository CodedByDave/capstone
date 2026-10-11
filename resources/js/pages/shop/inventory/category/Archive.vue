<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArchiveRestore,
    ArrowLeft,
    Inbox,
    Layers,
    Loader2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Category {
    id: number;
    name: string;
    description: string | null;
    deleted_at: string;
}

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const props = defineProps<{ archived: Category[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/shop/inventory' },
    { title: 'Categories', href: '/shop/inventory/category' },
    { title: 'Archive', href: '/shop/inventory/category/archive' },
];

const archivedList = ref<Category[]>([...props.archived]);
const restoringId = ref<number | null>(null);

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

async function restoreCategory(id: number) {
    restoringId.value = id;
    try {
        await axios.post(`${base.value}/category/${id}/restore`);
        archivedList.value = archivedList.value.filter((c) => c.id !== id);
        toast.success('Category restored successfully.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to restore category.', { autoClose: 4000 });
    } finally {
        restoringId.value = null;
    }
}
</script>

<template>
    <Head title="Archived Categories" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Categories">
        <div class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Categories</h2>
                    <p class="text-sm text-muted-foreground">
                        Restore categories to make them available again.
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit(`${base}/category`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Categories
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Layers class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span
                            class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal"
                        >
                            {{ archivedList.length }}
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Name
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Description
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Archived On
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="cat in archivedList"
                                    :key="cat.id"
                                    class="border-b opacity-70 transition-colors last:border-0 hover:bg-muted/20 hover:opacity-100"
                                >
                                    <td
                                        class="px-4 py-3 font-medium text-muted-foreground line-through"
                                    >
                                        {{ cat.name }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ cat.description ?? '—' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ formatDate(cat.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            :disabled="restoringId === cat.id"
                                            class="h-7 gap-1.5 text-xs"
                                            @click="restoreCategory(cat.id)"
                                        >
                                            <Loader2
                                                v-if="restoringId === cat.id"
                                                class="h-3 w-3 animate-spin"
                                            />
                                            <ArchiveRestore
                                                v-else
                                                class="h-3 w-3"
                                            />
                                            Restore
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="archivedList.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        <Inbox
                                            class="mx-auto mb-2 h-10 w-10 opacity-20"
                                        />
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
