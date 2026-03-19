<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref } from 'vue'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { ArrowLeft, Pencil, ShieldOff, ShieldCheck } from 'lucide-vue-next'

import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

const { shop } = defineProps<{
    shop: {
        id: number
        shop_name: string
        branch_name: string | null
        phone: string
        block_street: string
        municipality: string
        barangay: string
        postal_code: string
        status: string
        plan: string | null
        disable_reason: string | null
        created_at: string
        updated_at: string
        owner: { name: string; email: string }
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
    { title: shop.shop_name, href: `/admin/shop/${shop.id}` },
]

const planLabel: Record<string, { label: string; class: string }> = {
    basic:      { label: 'Basic',      class: 'bg-slate-100 text-slate-700' },
    standard:   { label: 'Standard',   class: 'bg-blue-100 text-blue-700' },
    premium:    { label: 'Premium',    class: 'bg-purple-100 text-purple-700' },
    enterprise: { label: 'Enterprise', class: 'bg-amber-100 text-amber-700' },
}

function getPlan(plan: string | null) {
    if (!plan) return { label: 'No Plan', class: 'bg-gray-100 text-gray-400' }
    return planLabel[plan] ?? { label: plan, class: 'bg-gray-100 text-gray-500' }
}

const disableDialogOpen = ref(false)
const disableReason = ref('')

function confirmDisable() {
    router.post(`/admin/shop/${shop.id}/disable`, {
        reason: disableReason.value,
    }, {
        onSuccess: () => {
            disableDialogOpen.value = false
            disableReason.value = ''
        },
    })
}

function enableShop() {
    router.post(`/admin/shop/${shop.id}/enable`)
}
</script>

<template>
    <Head :title="`${shop.shop_name} — Details`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Details">

        <div class="flex items-center justify-between mb-6">
            <Button variant="outline" @click="router.visit('/admin/shop')">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Shops
            </Button>

            <div class="flex gap-2">
                <!-- Disable / Enable -->
                <template v-if="shop.status !== 'disabled'">
                    <Button
                        variant="outline"
                        class="border-orange-400 text-orange-600 hover:bg-orange-50"
                        @click="disableDialogOpen = true"
                    >
                        <ShieldOff class="h-4 w-4 mr-2" />
                        Disable Shop
                    </Button>
                </template>
                <template v-else>
                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <Button variant="outline" class="border-green-500 text-green-600 hover:bg-green-50">
                                <ShieldCheck class="h-4 w-4 mr-2" />
                                Enable Shop
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Enable Shop</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Re-enable <strong>{{ shop.shop_name }}</strong>? They will regain full access to the system.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="enableShop">Enable</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </template>

                <Button @click="router.visit(`/admin/shop/${shop.id}/edit`)">
                    <Pencil class="h-4 w-4 mr-2" />
                    Edit Shop
                </Button>
            </div>
        </div>

        <!-- Disabled Warning Banner -->
        <div
            v-if="shop.status === 'disabled'"
            class="mb-4 flex items-start gap-3 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800"
        >
            <ShieldOff class="h-4 w-4 mt-0.5 shrink-0" />
            <div>
                <p class="font-semibold">This shop has been disabled.</p>
                <p v-if="shop.disable_reason" class="mt-0.5 text-orange-700">
                    Reason: {{ shop.disable_reason }}
                </p>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>{{ shop.shop_name }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Shop Name</p>
                        <p class="font-medium">{{ shop.shop_name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Branch Name</p>
                        <p class="font-medium">{{ shop.branch_name ?? '—' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Owner</p>
                        <p class="font-medium">{{ shop.owner.name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Email</p>
                        <p class="font-medium">{{ shop.owner.email }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Phone</p>
                        <p class="font-medium">{{ shop.phone }}</p>
                    </div>

                    <!-- Plan -->
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Subscribed Plan</p>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getPlan(shop.plan).class"
                        >
                            {{ getPlan(shop.plan).label }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Status</p>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold rounded-full text-white"
                            :class="{
                                'bg-green-500':  shop.status === 'active',
                                'bg-red-500':    shop.status === 'inactive',
                                'bg-yellow-500': shop.status === 'pending',
                                'bg-gray-500':   shop.status === 'disabled',
                            }"
                        >
                            {{ shop.status }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Block / Street</p>
                        <p class="font-medium">{{ shop.block_street }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Barangay</p>
                        <p class="font-medium">{{ shop.barangay }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Municipality</p>
                        <p class="font-medium">{{ shop.municipality }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Postal Code</p>
                        <p class="font-medium">{{ shop.postal_code }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Registered</p>
                        <p class="font-medium">{{ shop.created_at }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Last Updated</p>
                        <p class="font-medium">{{ shop.updated_at }}</p>
                    </div>

                    <!-- Disable Reason (only shown if disabled) -->
                    <div v-if="shop.disable_reason" class="space-y-1 md:col-span-2">
                        <p class="text-xs font-semibold uppercase text-muted-foreground">Disable Reason</p>
                        <p class="font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded px-3 py-2">
                            {{ shop.disable_reason }}
                        </p>
                    </div>

                </div>
            </CardContent>
        </Card>

        <!-- Disable Dialog -->
        <AlertDialog :open="disableDialogOpen" @update:open="disableDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="flex items-center gap-2 text-orange-600">
                        <ShieldOff class="h-5 w-5" />
                        Disable Shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        You are about to disable <strong>{{ shop.shop_name }}</strong>.
                        The owner will lose access. Please provide a reason.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <div class="mt-2">
                    <label class="text-sm font-medium">Reason for disabling</label>
                    <Textarea
                        v-model="disableReason"
                        class="mt-1"
                        placeholder="e.g. Violation of terms of service, fraudulent activity, etc."
                        rows="3"
                    />
                </div>

                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel @click="disableDialogOpen = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-orange-600 text-white hover:bg-orange-700"
                        :disabled="!disableReason.trim()"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
