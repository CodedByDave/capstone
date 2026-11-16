<script setup lang="ts">
import { StatusPill } from '@/components/data-table';
import PriceListStatusPill, {
    type PriceListStatus,
} from '@/components/supplier/PriceListStatusPill.vue';
import type { PortalPurchaseOrder } from '@/components/supplier/SupplierPurchaseOrders.vue';
import { Card, CardContent } from '@/components/ui/card';
import SupplierLayout from '@/layouts/supplier/SupplierLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ClipboardList, Package, ScanText } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    supplier: { name: string; shop_name: string | null };
    counts: { total: number; in_stock: number; out_of_stock: number };
    latestPriceList: { status: PriceListStatus } | null;
    purchaseOrders: { orders: PortalPurchaseOrder[]; awaiting: number };
}>();

const statCards = computed(() => [
    { label: 'Supplies listed', value: props.counts.total },
    { label: 'In stock', value: props.counts.in_stock },
    { label: 'Out of stock', value: props.counts.out_of_stock },
    { label: 'Orders awaiting you', value: props.purchaseOrders.awaiting },
]);

const shortcuts = [
    {
        title: 'Purchase orders',
        text: 'Confirm or decline orders the shop sent you.',
        href: '/supplier-portal/purchase-orders',
        icon: ClipboardList,
    },
    {
        title: 'Supplies',
        text: 'Keep your stock and prices current.',
        href: '/supplier-portal/supplies',
        icon: Package,
    },
    {
        title: 'Price list',
        text: 'Scan your printed price list and check it.',
        href: '/supplier-portal/price-list',
        icon: ScanText,
    },
];
</script>

<template>
    <SupplierLayout
        title="Dashboard"
        :supplier-name="supplier.name"
        :shop-name="supplier.shop_name"
    >
        <div>
            <h1 class="text-xl font-semibold">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Your supplies and orders at a glance.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            <Card v-for="item in statCards" :key="item.label">
                <CardContent class="p-4">
                    <p class="mb-3 text-xs font-medium text-muted-foreground">
                        {{ item.label }}
                    </p>
                    <p class="text-2xl font-bold">{{ item.value }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <p class="mb-3 text-xs font-medium text-muted-foreground">
                        Price list check
                    </p>
                    <PriceListStatusPill :status="latestPriceList?.status" />
                </CardContent>
            </Card>
        </div>

        <div v-if="purchaseOrders.awaiting > 0">
            <StatusPill tone="info">
                {{ purchaseOrders.awaiting }} order(s) waiting for your answer
            </StatusPill>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <Link
                v-for="item in shortcuts"
                :key="item.href"
                :href="item.href"
                class="group flex min-h-11 items-start gap-3 rounded-[14px] border bg-background p-4 hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <component
                    :is="item.icon"
                    class="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold">{{
                        item.title
                    }}</span>
                    <span class="block text-sm text-muted-foreground">{{
                        item.text
                    }}</span>
                </span>
                <ArrowRight
                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground group-hover:text-foreground"
                    aria-hidden="true"
                />
            </Link>
        </div>
    </SupplierLayout>
</template>
