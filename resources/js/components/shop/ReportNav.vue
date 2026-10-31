<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { router } from '@inertiajs/vue3';
import {
    Banknote,
    BarChart3,
    ClipboardList,
    Package,
    Tag,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{ active: string }>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/reports' : '/staff/reports',
);

const links = [
    { key: 'overview', label: 'Overview', icon: BarChart3, path: '' },
    {
        key: 'operations',
        label: 'Operations',
        icon: ClipboardList,
        path: '/operations',
    },
    { key: 'finance', label: 'Finance', icon: Banknote, path: '/finance' },
    { key: 'pricing', label: 'Pricing', icon: Tag, path: '/pricing' },
    { key: 'inventory', label: 'Inventory', icon: Package, path: '/inventory' },
    { key: 'employee', label: 'Employees', icon: Users, path: '/employee' },
];
</script>

<template>
    <nav class="flex flex-wrap gap-2" aria-label="Report sections">
        <Button
            v-for="link in links"
            :key="link.key"
            :variant="active === link.key ? 'default' : 'outline'"
            size="sm"
            :aria-current="active === link.key ? 'page' : undefined"
            @click="router.visit(base + link.path)"
        >
            <component :is="link.icon" class="mr-1.5 h-4 w-4" />
            {{ link.label }}
        </Button>
    </nav>
</template>
