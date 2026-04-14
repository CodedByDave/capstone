<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'

interface Supplier {
    id: number; name: string; contact_person: string | null
    email: string | null; phone: string | null; address: string | null
    status: string; notes: string | null
}

const { supplier } = defineProps<{ supplier: Supplier }>()

const page = usePage()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: `${baseRoute.value}/inventory` },
    { title: 'Suppliers', href: `${baseRoute.value}/supplier` },
    { title: 'Edit', href: `${baseRoute.value}/supplier/${supplier.id}/edit` },
]

const form = useForm({
    name:           supplier.name,
    contact_person: supplier.contact_person ?? '',
    email:          supplier.email ?? '',
    phone:          supplier.phone ?? '',
    address:        supplier.address ?? '',
    status:         supplier.status,
    notes:          supplier.notes ?? '',
})

function submit() {
    form.put(`${baseRoute.value}/supplier/${supplier.id}`)
}
</script>

<template>
    <Head :title="`Edit — ${supplier.name}`" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Supplier">
        <div class="px-6 space-y-8">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Edit Supplier</h2>
                    <p class="text-sm text-muted-foreground">Update details for <span class="font-medium text-foreground">{{ supplier.name }}</span>.</p>
                </div>
                <Button variant="outline" @click="router.visit(`${baseRoute}/supplier`)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Supplier Information</p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">

                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Supplier Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.name" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-6 space-y-1">
                        <label class="text-sm font-medium">Contact Person</label>
                        <Input v-model="form.contact_person" />
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Email</label>
                        <Input v-model="form.email" type="email" :class="{ 'border-red-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <Input v-model="form.phone" />
                    </div>

                    <div class="col-span-12 sm:col-span-4 space-y-1">
                        <label class="text-sm font-medium">Status</label>
                        <Select v-model="form.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Address</label>
                        <Input v-model="form.address" />
                    </div>

                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Notes</label>
                        <Textarea v-model="form.notes" rows="2" />
                    </div>

                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <Button variant="outline" :disabled="form.processing" @click="router.visit(`${baseRoute}/supplier`)">Cancel</Button>
                <Button :disabled="form.processing" @click="submit">
                    <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
