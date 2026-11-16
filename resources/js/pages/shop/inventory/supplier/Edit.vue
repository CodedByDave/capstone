<script setup lang="ts">
import { StatusPill } from '@/components/data-table';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Clock,
    Loader2,
    Mail,
    MapPin,
    Phone,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Supplier {
    public_id: string;
    user_id: number | null;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    lead_time_days: number;
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
    {
        title: 'Edit',
        href: `${baseRoute.value}/supplier/${supplier.public_id}/edit`,
    },
];

const form = useForm({
    name: supplier.name,
    contact_person: supplier.contact_person ?? '',
    email: supplier.email ?? '',
    phone: supplier.phone ?? '',
    address: supplier.address ?? '',
    lead_time_days: supplier.lead_time_days ?? 3,
    status: supplier.status,
    notes: supplier.notes ?? '',
});

function submit() {
    form.put(`${baseRoute.value}/supplier/${supplier.public_id}`);
}

const initials = computed(
    () =>
        (form.name || supplier.name)
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((word) => word[0]?.toUpperCase())
            .join('') || '?',
);

const saveStatus = computed(() => {
    if (form.processing) return 'Saving…';
    if (form.recentlySuccessful) return 'Changes saved';

    return form.isDirty ? 'You have unsaved changes' : 'No changes yet';
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];
</script>

<template>
    <Head :title="`Edit — ${supplier.name}`" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Supplier">
        <div class="space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Edit supplier
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update details for
                        <span class="font-medium text-foreground">{{
                            supplier.name
                        }}</span
                        >.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="back"
                    class="shrink-0"
                    @click="router.visit(`${baseRoute}/supplier`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to suppliers
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Left column -->
                <div class="space-y-8">
                    <!-- Supplier -->
                    <section>
                        <h3 class="text-base font-semibold">Supplier</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Who you buy from.
                        </p>

                        <div class="mt-4 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label
                                    for="supplier-name"
                                    class="text-sm font-medium"
                                    >Supplier name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="supplier-name"
                                    v-model="form.name"
                                    :class="{
                                        'border-red-500': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <label
                                    for="supplier-contact"
                                    class="text-sm font-medium"
                                    >Contact person</label
                                >
                                <Input
                                    id="supplier-contact"
                                    v-model="form.contact_person"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Contact -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Contact details</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            How to reach them.
                        </p>

                        <div class="mt-4 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label
                                    for="supplier-email"
                                    class="text-sm font-medium"
                                    >Email
                                    <span
                                        v-if="supplier.user_id"
                                        class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <Input
                                    id="supplier-email"
                                    v-model="form.email"
                                    type="email"
                                    :required="!!supplier.user_id"
                                    :class="{
                                        'border-red-500': form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="supplier.user_id"
                                    class="text-xs text-muted-foreground"
                                >
                                    The supplier signs in with this email.
                                </p>
                                <p
                                    v-if="form.errors.email"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <label
                                    for="supplier-phone"
                                    class="text-sm font-medium"
                                    >Phone</label
                                >
                                <PhilippinePhoneInput
                                    id="supplier-phone"
                                    v-model="form.phone"
                                />
                            </div>

                            <div class="space-y-1 sm:col-span-2">
                                <label
                                    for="supplier-address"
                                    class="text-sm font-medium"
                                    >Address</label
                                >
                                <Input
                                    id="supplier-address"
                                    v-model="form.address"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Ordering -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Ordering</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            How long they take, and whether you still order from
                            them.
                        </p>

                        <div class="mt-4 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label
                                    for="supplier-lead"
                                    class="text-sm font-medium"
                                    >Lead time (days)</label
                                >
                                <Input
                                    id="supplier-lead"
                                    v-model.number="form.lead_time_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Days from ordering to receiving stock. Used
                                    to suggest reorder quantities.
                                </p>
                                <p
                                    v-if="form.errors.lead_time_days"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.lead_time_days }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span
                                    id="supplier-status-label"
                                    class="text-sm font-medium"
                                    >Status</span
                                >
                                <div
                                    role="radiogroup"
                                    aria-labelledby="supplier-status-label"
                                    class="grid grid-cols-2 gap-2"
                                >
                                    <button
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        type="button"
                                        role="radio"
                                        :aria-checked="
                                            form.status === option.value
                                        "
                                        class="h-10 rounded-md border text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        :class="
                                            form.status === option.value
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : 'border-input text-muted-foreground hover:bg-muted'
                                        "
                                        @click="form.status = option.value"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                                <p
                                    v-if="form.errors.status"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Notes -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Notes</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Anything the team should know when ordering.
                        </p>

                        <div class="mt-4">
                            <label for="supplier-notes" class="sr-only"
                                >Notes</label
                            >
                            <Textarea
                                id="supplier-notes"
                                v-model="form.notes"
                                rows="3"
                            />
                        </div>
                    </section>
                </div>

                <!-- Right column -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <section>
                        <p
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Summary
                        </p>

                        <div class="mt-3 flex items-center gap-3">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
                                aria-hidden="true"
                                >{{ initials }}</span
                            >
                            <div class="min-w-0 space-y-1">
                                <p class="truncate text-base font-bold">
                                    {{ form.name || 'Supplier name' }}
                                </p>
                                <StatusPill
                                    :tone="
                                        form.status === 'active'
                                            ? 'success'
                                            : 'neutral'
                                    "
                                >
                                    {{
                                        form.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </StatusPill>
                            </div>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm">
                            <li class="flex items-start gap-2">
                                <User
                                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span class="min-w-0 break-words">{{
                                    form.contact_person || '—'
                                }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Mail
                                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span class="min-w-0 break-all">{{
                                    form.email || '—'
                                }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Phone
                                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span>{{ form.phone || '—' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <MapPin
                                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span class="min-w-0 break-words">{{
                                    form.address || '—'
                                }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Clock
                                    class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span
                                    >{{ form.lead_time_days || 0 }}-day lead
                                    time</span
                                >
                            </li>
                        </ul>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-sm text-muted-foreground"
                            role="status"
                            aria-live="polite"
                        >
                            {{ saveStatus }}
                        </p>

                        <Button
                            type="submit"
                            class="mt-3 w-full"
                            :disabled="!form.isDirty || form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save changes' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="form.processing"
                            @click="router.visit(`${baseRoute}/supplier`)"
                        >
                            Cancel
                        </Button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
