<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import { router } from '@inertiajs/vue3'
import {
    UserCircle, Package, ClipboardList,
    Tags, BarChart3, ShieldAlert, ChevronRight,
} from 'lucide-vue-next'

const { can, canAccessModule } = usePermissions()

const moduleIconMap: Record<string, { icon: any; href: string; description: string; color: string }> = {
    'Employee Management':  { icon: UserCircle,    href: '/staff/employee',  description: 'View and manage employees',    color: 'bg-blue-100 text-blue-600'   },
    'Inventory Management': { icon: Package,       href: '/staff/inventory', description: 'Track stock and supplies',     color: 'bg-teal-100 text-teal-600'   },
    'Order Management':     { icon: ClipboardList, href: '/staff/orders',    description: 'Process customer orders',      color: 'bg-orange-100 text-orange-600'},
    'Services & Pricing':   { icon: Tags,          href: '/staff/services',  description: 'Manage services and pricing',  color: 'bg-yellow-100 text-yellow-600'},
    'Reports & Analytics':  { icon: BarChart3,     href: '/staff/reports',   description: 'View reports and analytics',   color: 'bg-green-100 text-green-600' },
}

const actionColorMap: Record<string, string> = {
    view:    'bg-blue-50 text-blue-700 border border-blue-200',
    create:  'bg-green-50 text-green-700 border border-green-200',
    update:  'bg-yellow-50 text-yellow-700 border border-yellow-200',
    archive: 'bg-red-50 text-red-700 border border-red-200',
    delete:  'bg-red-50 text-red-700 border border-red-200',
    export:  'bg-purple-50 text-purple-700 border border-purple-200',
    import:  'bg-indigo-50 text-indigo-700 border border-indigo-200',
    manage:  'bg-orange-50 text-orange-700 border border-orange-200',
}

const props = defineProps<{
    permissions: Record<string, string[]>
}>()

const accessibleModules = Object.entries(props.permissions)
    .filter(([, actions]) => actions.length > 0)
    .map(([module, actions]) => ({
        module,
        actions,
        ...(moduleIconMap[module] ?? { icon: Package, href: '#', description: '', color: 'bg-gray-100 text-gray-600' }),
    }))
</script>

<template>
    <Head title="Staff Dashboard" />

    <ShopLayout title="Dashboard">
        <div class="p-4 sm:p-6 space-y-6">

            <!-- Header -->
            <div>
                <h2 class="text-lg font-semibold">Welcome back!</h2>
                <p class="text-sm text-muted-foreground">Here's what you have access to.</p>
            </div>

            <!-- No permissions -->
            <div
                v-if="accessibleModules.length === 0"
                class="flex flex-col items-center justify-center py-20 text-center px-4"
            >
                <ShieldAlert class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="font-semibold text-lg">No permissions assigned</h3>
                <p class="text-sm text-muted-foreground mt-1 max-w-xs">
                    Your shop owner hasn't assigned any permissions to your role yet.
                </p>
            </div>

            <!-- Module cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <button
                    v-for="item in accessibleModules"
                    :key="item.module"
                    class="group relative flex flex-col gap-4 rounded-2xl border bg-card p-5 text-left
                           shadow-sm hover:shadow-md hover:border-primary/40 transition-all duration-200
                           active:scale-[0.98] w-full min-w-0"
                    @click="router.visit(item.href)"
                >
                    <!-- Top row: icon + title + arrow -->
                    <div class="flex items-start justify-between gap-3 min-w-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div :class="['rounded-xl p-2.5 shrink-0', item.color]">
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                            <span class="font-semibold text-sm leading-tight break-words min-w-0">
                                {{ item.module }}
                            </span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5
                                            group-hover:translate-x-0.5 transition-transform" />
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        {{ item.description }}
                    </p>

                    <!-- Action badges -->
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="action in item.actions"
                            :key="action"
                            class="text-xs px-2 py-0.5 rounded-full capitalize font-medium"
                            :class="actionColorMap[action] ?? 'bg-muted text-muted-foreground'"
                        >
                            {{ action }}
                        </span>
                    </div>
                </button>
            </div>

        </div>
    </ShopLayout>
</template>
