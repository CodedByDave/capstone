<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { Download } from 'lucide-vue-next';
import { computed } from 'vue';

/** Downloads one dataset of the shop's own data as CSV (see ShopDataExportService::datasets()). */
const props = withDefaults(defineProps<{ dataset: string; label?: string }>(), {
    label: 'Export CSV',
});

const { isOwner } = usePermissions();
const href = computed(
    () => `${isOwner.value ? '/shop' : '/staff'}/export/${props.dataset}`,
);
</script>

<template>
    <Button as-child variant="outline" size="sm" class="gap-1.5">
        <a :href="href" download>
            <Download class="h-4 w-4" aria-hidden="true" />
            {{ label }}
        </a>
    </Button>
</template>
