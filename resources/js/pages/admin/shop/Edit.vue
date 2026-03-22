<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

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
        owner: { name: string; email: string }
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
    { title: shop.shop_name, href: `/admin/shop/${shop.id}` },
    { title: 'Edit', href: `/admin/shop/${shop.id}/edit` },
]

const form = useForm({
    shop_name:    shop.shop_name,
    branch_name:  shop.branch_name ?? '',
    phone:        shop.phone,
    block_street: shop.block_street,
    municipality: shop.municipality,
    barangay:     shop.barangay,
    postal_code:  shop.postal_code,
    status:       shop.status,
})

function submit() {
    form.put(`/admin/shop/${shop.id}`, {
        onSuccess: () => {
            toast.success('Shop updated successfully.', { autoClose: 3000 })
            router.visit(`/admin/shop/${shop.id}`)
        },
        onError: () => {
            toast.error('Failed to update shop. Please check the form.', { autoClose: 4000 })
        },
    })
}
</script>

<template>
    <Head :title="`Edit — ${shop.shop_name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Edit Shop">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-semibold">Edit Laundry / Shop</h2>
                <p class="text-sm text-muted-foreground mt-1">Update the details for {{ shop.shop_name }}</p>
            </div>
            <Button variant="outline" @click="router.visit(`/admin/shop/${shop.id}`)">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to Details
            </Button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Shop Name (full width) -->
            <div class="space-y-1 md:col-span-2">
                <label class="text-sm font-medium">Laundry / Shop Name</label>
                <Input v-model="form.shop_name" />
                <p v-if="form.errors.shop_name" class="text-xs text-red-500">{{ form.errors.shop_name }}</p>
            </div>

            <!-- Branch Name (full width) -->
            <div class="space-y-1 md:col-span-2">
                <label class="text-sm font-medium">
                    Branch Name
                    <span class="text-muted-foreground">(optional)</span>
                </label>
                <Input v-model="form.branch_name" />
                <p v-if="form.errors.branch_name" class="text-xs text-red-500">{{ form.errors.branch_name }}</p>
            </div>

            <!-- Owner Name (read-only, full width) -->
            <div class="space-y-1 md:col-span-2">
                <label class="text-sm font-medium">Owner Name</label>
                <Input :model-value="shop.owner.name" disabled />
                <p class="text-xs text-muted-foreground mt-0.5">Owner info is managed via user accounts.</p>
            </div>

            <!-- Phone -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Phone Number</label>
                <Input v-model="form.phone" />
                <p v-if="form.errors.phone" class="text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <!-- Status -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Status</label>
                <Select v-model="form.status">
                    <SelectTrigger>
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="disabled">Disabled</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</p>
            </div>

            <!-- Block / Street -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Block / Street</label>
                <Input v-model="form.block_street" />
                <p v-if="form.errors.block_street" class="text-xs text-red-500">{{ form.errors.block_street }}</p>
            </div>

            <!-- Barangay -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Barangay</label>
                <Input v-model="form.barangay" />
                <p v-if="form.errors.barangay" class="text-xs text-red-500">{{ form.errors.barangay }}</p>
            </div>

            <!-- Municipality -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Municipality</label>
                <Input v-model="form.municipality" />
                <p v-if="form.errors.municipality" class="text-xs text-red-500">{{ form.errors.municipality }}</p>
            </div>

            <!-- Postal Code -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Postal Code</label>
                <Input v-model="form.postal_code" />
                <p v-if="form.errors.postal_code" class="text-xs text-red-500">{{ form.errors.postal_code }}</p>
            </div>

        </div>

        <!-- Note about subscription -->
        <div class="mt-6 rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            Subscription plan is managed through the Orders system and cannot be edited here.
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-3 mt-8 border-t pt-6">
            <Button variant="outline" @click="router.visit(`/admin/shop/${shop.id}`)">
                Cancel
            </Button>
            <Button :disabled="form.processing" @click="submit">
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>

    </AdminLayout>
</template>
