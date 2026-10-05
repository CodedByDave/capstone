<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

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
import {
    Bike,
    ChevronLeft,
    Loader2,
    PackageCheck,
    Pencil,
    Phone,
    Plus,
    Trash2,
    Truck,
    User,
    X,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Rider {
    id: number;
    name: string;
    phone: string | null;
    vehicle_type: string | null;
    status: 'active' | 'inactive';
    deliveries_count: number;
    created_at: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    riders: Rider[];
}>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
onMounted(() => {
    const flash = (page.props as any).toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'success') toast.success(flash.message);
    else toast.error(flash.message);
});

// ─── Add Rider Modal ──────────────────────────────────────────────────────────

const showAdd = ref(false);
const adding = ref(false);

const addForm = ref({ name: '', phone: '', vehicle_type: '' });

function openAdd() {
    addForm.value = { name: '', phone: '', vehicle_type: '' };
    showAdd.value = true;
}

function submitAdd() {
    adding.value = true;
    router.post(
        '/shop/logistics/riders',
        {
            name: addForm.value.name,
            phone: addForm.value.phone || null,
            vehicle_type: addForm.value.vehicle_type || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAdd.value = false;
            },
            onFinish: () => {
                adding.value = false;
            },
        },
    );
}

// ─── Edit Rider Modal ─────────────────────────────────────────────────────────

const editModal = ref<{ open: boolean; rider: Rider | null; form: any }>({
    open: false,
    rider: null,
    form: { name: '', phone: '', vehicle_type: '', status: 'active' },
});
const saving = ref(false);

function openEdit(rider: Rider) {
    editModal.value = {
        open: true,
        rider,
        form: {
            name: rider.name,
            phone: rider.phone ?? '',
            vehicle_type: rider.vehicle_type ?? '',
            status: rider.status,
        },
    };
}

function submitEdit() {
    if (!editModal.value.rider) return;
    saving.value = true;
    router.patch(
        `/shop/logistics/riders/${editModal.value.rider.id}`,
        {
            name: editModal.value.form.name,
            phone: editModal.value.form.phone || null,
            vehicle_type: editModal.value.form.vehicle_type || null,
            status: editModal.value.form.status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                editModal.value.open = false;
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref<number | null>(null);

function deleteRider(id: number) {
    if (!confirm('Remove this rider? Their delivery records will remain.'))
        return;
    deleting.value = id;
    router.delete(`/shop/logistics/riders/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = null;
        },
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function fmtDate(d: string) {
    return new Date(d).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Riders" />

    <ShopLayout title="Riders">
        <div class="space-y-6 px-6">
            <!-- ── Header ── -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="router.visit('/shop/logistics')"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h2 class="text-lg font-semibold">Riders</h2>
                        <p class="text-sm text-muted-foreground">
                            Manage your delivery riders.
                        </p>
                    </div>
                </div>
                <Button size="sm" @click="openAdd">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Add Rider
                </Button>
            </div>

            <!-- ── Empty State ── -->
            <div
                v-if="props.riders.length === 0"
                class="flex flex-col items-center gap-3 rounded-xl border border-dashed py-16 text-center"
            >
                <Bike class="h-10 w-10 text-muted-foreground/30" />
                <div>
                    <p class="text-sm font-medium">No riders yet</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Add your first rider to start assigning deliveries.
                    </p>
                </div>
                <Button size="sm" variant="outline" @click="openAdd">
                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                    Add Rider
                </Button>
            </div>

            <!-- ── Riders Grid ── -->
            <div
                v-else
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="rider in props.riders"
                    :key="rider.id"
                    class="flex flex-col gap-3 rounded-xl border bg-card p-4"
                >
                    <!-- Top row -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted"
                            >
                                <User class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm leading-tight font-semibold">
                                    {{ rider.name }}
                                </p>
                                <span
                                    class="mt-0.5 inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-xs"
                                    :class="
                                        rider.status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="
                                            rider.status === 'active'
                                                ? 'bg-green-500'
                                                : 'bg-gray-400'
                                        "
                                    />
                                    {{
                                        rider.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                class="flex h-7 w-7 items-center justify-center rounded-lg border transition-colors hover:bg-muted"
                                @click="openEdit(rider)"
                            >
                                <Pencil
                                    class="h-3.5 w-3.5 text-muted-foreground"
                                />
                            </button>
                            <button
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 transition-colors hover:bg-red-50"
                                :disabled="deleting === rider.id"
                                @click="deleteRider(rider.id)"
                            >
                                <Loader2
                                    v-if="deleting === rider.id"
                                    class="h-3.5 w-3.5 animate-spin text-muted-foreground"
                                />
                                <Trash2
                                    v-else
                                    class="h-3.5 w-3.5 text-red-500"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-1.5 text-xs text-muted-foreground">
                        <div
                            v-if="rider.phone"
                            class="flex items-center gap-1.5"
                        >
                            <Phone class="h-3.5 w-3.5 shrink-0" />
                            {{ rider.phone }}
                        </div>
                        <div
                            v-if="rider.vehicle_type"
                            class="flex items-center gap-1.5"
                        >
                            <Truck class="h-3.5 w-3.5 shrink-0" />
                            {{ rider.vehicle_type }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <PackageCheck class="h-3.5 w-3.5 shrink-0" />
                            {{ rider.deliveries_count }}
                            {{
                                rider.deliveries_count === 1
                                    ? 'delivery'
                                    : 'deliveries'
                            }}
                        </div>
                    </div>

                    <!-- Footer -->
                    <p
                        class="mt-auto border-t pt-2 text-xs text-muted-foreground/60"
                    >
                        Added {{ fmtDate(rider.created_at) }}
                    </p>
                </div>
            </div>
        </div>
    </ShopLayout>

    <!-- ── Add Rider Modal ────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="showAdd"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="showAdd = false"
            >
                <div
                    class="w-full max-w-sm overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <p class="text-sm font-semibold">Add Rider</p>
                        <button
                            @click="showAdd = false"
                            class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-3 px-5 py-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Full Name
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="addForm.name"
                                placeholder="Juan dela Cruz"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Phone</label>
                            <PhilippinePhoneInput
                                v-model="addForm.phone"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Vehicle Type</label
                            >
                            <Input
                                v-model="addForm.vehicle_type"
                                placeholder="e.g. Motorcycle, Bicycle"
                                class="h-9 text-sm"
                            />
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t bg-muted/20 px-5 py-4"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            @click="showAdd = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :disabled="adding || !addForm.name"
                            @click="submitAdd"
                        >
                            <Loader2
                                v-if="adding"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ adding ? 'Adding…' : 'Add Rider' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ── Edit Rider Modal ───────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="editModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="editModal.open = false"
            >
                <div
                    class="w-full max-w-sm overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <div>
                            <p class="text-sm font-semibold">Edit Rider</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ editModal.rider?.name }}
                            </p>
                        </div>
                        <button
                            @click="editModal.open = false"
                            class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-3 px-5 py-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Full Name
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="editModal.form.name"
                                placeholder="Juan dela Cruz"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Phone</label>
                            <PhilippinePhoneInput
                                v-model="editModal.form.phone"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Vehicle Type</label
                            >
                            <Input
                                v-model="editModal.form.vehicle_type"
                                placeholder="e.g. Motorcycle, Bicycle"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Status</label>
                            <Select v-model="editModal.form.status">
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active"
                                        >Active</SelectItem
                                    >
                                    <SelectItem value="inactive"
                                        >Inactive</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t bg-muted/20 px-5 py-4"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            @click="editModal.open = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :disabled="saving || !editModal.form.name"
                            @click="submitEdit"
                        >
                            <Loader2
                                v-if="saving"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ saving ? 'Saving…' : 'Save Changes' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
