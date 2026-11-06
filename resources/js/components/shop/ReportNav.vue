<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { router, usePage } from '@inertiajs/vue3';
import {
    Banknote,
    BarChart3,
    ClipboardList,
    Lock,
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

// Basic reports come with every plan; Finance, Pricing and Employees are advanced (Premium plan).
const page = usePage();
const hasAdvanced = computed(
    () => !!(page.props as any).shopCapabilities?.advanced_reports,
);

const links = [
    { key: 'overview', label: 'Overview', icon: BarChart3, path: '' },
    {
        key: 'operations',
        label: 'Operations',
        icon: ClipboardList,
        path: '/operations',
    },
    {
        key: 'finance',
        label: 'Finance',
        icon: Banknote,
        path: '/finance',
        advanced: true,
    },
    {
        key: 'pricing',
        label: 'Pricing',
        icon: Tag,
        path: '/pricing',
        advanced: true,
    },
    { key: 'inventory', label: 'Inventory', icon: Package, path: '/inventory' },
    {
        key: 'employee',
        label: 'Employees',
        icon: Users,
        path: '/employee',
        advanced: true,
    },
];
</script>

<template>
    <nav class="flex flex-wrap gap-2" aria-label="Report sections">
        <Button
            v-for="link in links"
            :key="link.key"
            :variant="active === link.key ? 'default' : 'outline'"
            size="sm"
            :title="
                link.advanced && !hasAdvanced
                    ? 'Advanced report — included in the Premium plan'
                    : undefined
            "
            :aria-current="active === link.key ? 'page' : undefined"
            @click="router.visit(base + link.path)"
        >
            <component :is="link.icon" class="mr-1.5 h-4 w-4" />
            {{ link.label }}
            <template v-if="link.advanced && !hasAdvanced">
                <Lock class="ml-1.5 h-3.5 w-3.5" aria-hidden="true" />
                <span class="sr-only">(Premium plan)</span>
            </template>
        </Button>
    </nav>
</template>
