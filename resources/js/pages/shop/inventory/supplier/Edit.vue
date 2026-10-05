<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Supplier {
    id: number;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    status: string;
    notes: string | null;
}

const { supplier } = defineProps<{ supplier: Supplier }>();

const page = usePage();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: `${baseRoute.value}/inventory` },
    { title: 'Suppliers', href: `${baseRoute.value}/supplier` },
    { title: 'Edit', href: `${baseRoute.value}/supplier/${supplier.id}/edit` },
];

const form = useForm({
    name: supplier.name,
    contact_person: supplier.contact_person ?? '',
    email: supplier.email ?? '',
    phone: supplier.phone ?? '',
    address: supplier.address ?? '',
    status: supplier.status,
    notes: supplier.notes ?? '',
});

function submit() {
    form.put(`${baseRoute.value}/supplier/${supplier.id}`);
}
</script>

<template>
    <Head :title="`Edit — ${supplier.name}`" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Supplier">
        <div class="space-y-8 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Edit Supplier</h2>
                    <p class="text-sm text-muted-foreground">
                        Update details for
                        <span class="font-medium text-foreground">{{
                            supplier.name
                        }}</span
                        >.
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit(`${baseRoute}/supplier`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back
                </Button>
            </div>

            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Supplier Information
                </p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-6">
                        <label class="text-sm font-medium"
                            >Supplier Name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.name"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="col-span-12 space-y-1 sm:col-span-6">
                        <label class="text-sm font-medium"
                            >Contact Person</label
                        >
                        <Input v-model="form.contact_person" />
                    </div>

                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium">Email</label>
                        <Input
                            v-model="form.email"
                            type="email"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium">Phone</label>
                        <PhilippinePhoneInput v-model="form.phone" />
                    </div>

                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium">Status</label>
                        <Select v-model="form.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive"
                                    >Inactive</SelectItem
                                >
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

            <div class="flex items-center justify-end gap-3 border-t pt-4">
                <Button
                    variant="outline"
                    :disabled="form.processing"
                    @click="router.visit(`${baseRoute}/supplier`)"
                    >Cancel</Button
                >
                <Button :disabled="form.processing" @click="submit">
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </div>
    </ShopLayout>
</template>
