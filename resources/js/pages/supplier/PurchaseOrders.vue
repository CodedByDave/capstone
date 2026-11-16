<script setup lang="ts">
import SupplierPurchaseOrders, {
    type PortalPurchaseOrder,
} from '@/components/supplier/SupplierPurchaseOrders.vue';
import SupplierLayout from '@/layouts/supplier/SupplierLayout.vue';

defineProps<{
    supplier: { name: string; shop_name: string | null };
    purchaseOrders: { orders: PortalPurchaseOrder[]; awaiting: number };
}>();
</script>

<template>
    <SupplierLayout
        title="Purchase orders"
        :supplier-name="supplier.name"
        :shop-name="supplier.shop_name"
    >
        <SupplierPurchaseOrders
            :orders="purchaseOrders.orders"
            :awaiting="purchaseOrders.awaiting"
            :shop-name="supplier.shop_name"
        />
    </SupplierLayout>
</template>
