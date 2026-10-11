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
    Loader2,
    Mail,
    Phone,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Supplier {
    id: number;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    status: string;
    inventory_count?: number;
    deleted_at: string;
}

interface Paginator {
    data: Supplier[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ archived: Paginator }>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/supplier' : '/staff/supplier',
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Suppliers', href: base.value },
    { title: 'Archive', href: `${base.value}/archive` },
];

const restoringId = ref<number | null>(null);
const archivedList = ref([...props.archived.data]);

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

async function restoreSupplier(id: number) {
    restoringId.value = id;
    try {
        await axios.post(`${base.value}/${id}/restore`);
        archivedList.value = archivedList.value.filter((s) => s.id !== id);
        toast.success('Supplier restored successfully.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to restore supplier.', { autoClose: 4000 });
    } finally {
        restoringId.value = null;
    }
}
</script>

<template>
    <Head title="Archived Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Suppliers">
        <div class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Suppliers</h2>
                    <p class="text-sm text-muted-foreground">
                        Restore suppliers to make them available again.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Suppliers
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Truck class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span
                            class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal"
                        >
                            {{ archived.total }}
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
                                        Supplier
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Contact
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Items
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
                                    v-for="s in archivedList"
                                    :key="s.id"
                                    class="border-b opacity-70 transition-colors last:border-0 hover:bg-muted/20 hover:opacity-100"
                                >
                                    <td class="px-4 py-3">
                                        <p
                                            class="font-medium text-muted-foreground line-through"
                                        >
                                            {{ s.name }}
                                        </p>
                                        <p
                                            v-if="s.contact_person"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ s.contact_person }}
                                        </p>
                                    </td>
                                    <td class="space-y-0.5 px-4 py-3">
                                        <div
                                            v-if="s.phone"
                                            class="flex items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <Phone class="h-3 w-3" />
                                            {{ s.phone }}
                                        </div>
                                        <div
                                            v-if="s.email"
                                            class="flex items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <Mail class="h-3 w-3" />
                                            {{ s.email }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ s.inventory_count ?? 0 }} items
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ formatDate(s.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            :disabled="restoringId === s.id"
                                            class="h-7 gap-1.5 text-xs"
                                            @click="restoreSupplier(s.id)"
                                        >
                                            <Loader2
                                                v-if="restoringId === s.id"
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
                                        colspan="5"
                                        class="px-4 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        <Inbox
                                            class="mx-auto mb-2 h-10 w-10 opacity-20"
                                        />
                                        No archived suppliers.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="archived.last_page > 1"
                        class="flex justify-end gap-1 pt-4"
                    >
                        <Button
                            v-for="link in archived.links"
                            :key="link.label"
                            size="sm"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            class="h-7 min-w-7 text-xs"
                            @click="link.url && router.visit(link.url)"
                        >
                            <span v-html="link.label" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
