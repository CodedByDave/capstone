<script setup lang="ts">
import AddressPinPicker from '@/components/customer/AddressPinPicker.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useFlashToast } from '@/composables/useFlashToast';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { ADDRESS_LABELS, type SavedAddress } from '@/types/saved-address';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Briefcase,
    Home,
    Loader2,
    MapPin,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import 'vue3-toastify/dist/index.css';

const props = defineProps<{
    addresses: SavedAddress[];
    limit: number;
}>();

// The server confirms each save, edit, default change and delete with a toast message.
useFlashToast();

const atLimit = computed(() => props.addresses.length >= props.limit);
const icons = { home: Home, work: Briefcase, other: MapPin };

// ─── Form dialog ──────────────────────────────────────────────────────────────

const dialogOpen = ref(false);
const editingId = ref<string | null>(null);

const form = useForm({
    label: 'home' as SavedAddress['label'],
    recipient_name: '',
    phone: '',
    address: '',
    latitude: null as number | null,
    longitude: null as number | null,
    is_default: false,
});

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_default = props.addresses.length === 0;
    autoAddress = '';
    dialogOpen.value = true;
}

function openEdit(address: SavedAddress) {
    editingId.value = address.id;
    form.clearErrors();
    form.label = address.label;
    form.recipient_name = address.recipient_name;
    form.phone = address.phone;
    form.address = address.address;
    form.latitude = address.latitude;
    form.longitude = address.longitude;
    form.is_default = address.is_default;
    autoAddress = '';
    dialogOpen.value = true;
}

// ─── Pin ──────────────────────────────────────────────────────────────────────

// The pinned point, shared with the map picker.
const pin = computed({
    get: () =>
        form.latitude === null || form.longitude === null
            ? null
            : { lat: form.latitude, lng: form.longitude },
    set: (point) => {
        form.latitude = point?.lat ?? null;
        form.longitude = point?.lng ?? null;
        if (point) form.clearErrors('latitude', 'longitude');
    },
});

// The address text the map filled in last. Moving the pin only replaces the text while the
// customer has not edited it, so a typed house number is never overwritten.
let autoAddress = '';

function onAddressFound(text: string) {
    if (form.address.trim() === '' || form.address === autoAddress) {
        form.address = text;
        autoAddress = text;
    }
}

function save() {
    if (!pin.value) {
        form.setError('latitude', 'Pin your exact location on the map.');
        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
        },
    };

    if (editingId.value) {
        form.put(`/user/addresses/${editingId.value}`, options);
    } else {
        form.post('/user/addresses', options);
    }
}

// ─── Row actions ──────────────────────────────────────────────────────────────

const busyId = ref<string | null>(null);

function makeDefault(address: SavedAddress) {
    busyId.value = address.id;
    router.post(
        `/user/addresses/${address.id}/default`,
        {},
        {
            preserveScroll: true,
            onFinish: () => (busyId.value = null),
        },
    );
}

const deleting = ref<SavedAddress | null>(null);

function confirmDelete() {
    if (!deleting.value) return;
    router.delete(`/user/addresses/${deleting.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}

const inputClass =
    'mt-1 block min-h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-[15px] text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/25 focus:outline-none';
</script>

<template>
    <Head title="My addresses" />
    <UserLayout>
        <div class="px-5 pt-6 pb-10">
            <Link
                href="/user/profile"
                class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-slate-600 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
            >
                <ArrowLeft class="size-4" aria-hidden="true" />
                Profile
            </Link>

            <h1
                class="mt-2 font-display text-[22px] leading-7 font-bold text-slate-900"
            >
                My addresses
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Save up to {{ limit }} addresses and pick one each time you
                order. Orders keep the address you chose, even if you change it
                here later.
            </p>

            <ul v-if="addresses.length" class="mt-6 space-y-3">
                <li
                    v-for="address in addresses"
                    :key="address.id"
                    class="rounded-xl border border-slate-200 p-4"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <component
                            :is="icons[address.label]"
                            class="size-4 text-blue-600"
                            aria-hidden="true"
                        />
                        <span class="text-sm font-semibold text-slate-900">
                            {{ ADDRESS_LABELS[address.label] }}
                        </span>
                        <span
                            v-if="address.is_default"
                            class="rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700"
                        >
                            Default
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-900">
                        {{ address.recipient_name }} · {{ address.phone }}
                    </p>
                    <p class="mt-0.5 text-sm break-words text-slate-500">
                        {{ address.address }}
                    </p>

                    <div
                        class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-100 pt-2"
                    >
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                            :aria-label="`Edit ${ADDRESS_LABELS[address.label]} address`"
                            @click="openEdit(address)"
                        >
                            <Pencil class="size-4" aria-hidden="true" />
                            Edit
                        </button>
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-red-600 hover:text-red-700 focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:outline-none"
                            :aria-label="`Delete ${ADDRESS_LABELS[address.label]} address`"
                            @click="deleting = address"
                        >
                            <Trash2 class="size-4" aria-hidden="true" />
                            Delete
                        </button>
                        <button
                            v-if="!address.is_default"
                            type="button"
                            class="ml-auto inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-slate-600 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none disabled:opacity-60"
                            :disabled="busyId === address.id"
                            @click="makeDefault(address)"
                        >
                            <Loader2
                                v-if="busyId === address.id"
                                class="size-4 animate-spin"
                            />
                            Set as default
                        </button>
                    </div>
                </li>
            </ul>

            <div
                v-else
                class="mt-6 flex flex-col items-center rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center"
            >
                <MapPin class="size-8 text-slate-400" aria-hidden="true" />
                <p class="mt-3 text-sm font-semibold text-slate-900">
                    No saved addresses yet
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Add one so choosing a pickup address takes a single tap.
                </p>
            </div>

            <button
                type="button"
                class="mt-5 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl text-sm font-semibold transition focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-none"
                :class="
                    atLimit
                        ? 'cursor-not-allowed bg-slate-100 text-slate-400'
                        : 'bg-blue-600 text-white hover:bg-blue-700'
                "
                :disabled="atLimit"
                @click="openCreate"
            >
                <Plus class="size-4" aria-hidden="true" />
                Add new address
            </button>
            <p v-if="atLimit" class="mt-2 text-center text-xs text-slate-500">
                You have saved {{ addresses.length }} of {{ limit }} addresses.
                Delete one to add another.
            </p>
        </div>

        <!-- Add / edit -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-h-[90svh] overflow-y-auto sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingId ? 'Edit address' : 'New address' }}
                    </DialogTitle>
                    <DialogDescription>
                        The rider uses this to find you and to call the person
                        receiving them.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="save">
                    <fieldset>
                        <legend class="text-xs font-medium text-slate-500">
                            Label
                        </legend>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            <label
                                v-for="(text, value) in ADDRESS_LABELS"
                                :key="value"
                                class="flex min-h-11 cursor-pointer items-center justify-center rounded-xl border text-sm font-semibold transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-blue-600"
                                :class="
                                    form.label === value
                                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                                        : 'border-slate-200 text-slate-600 hover:border-slate-300'
                                "
                            >
                                <input
                                    v-model="form.label"
                                    type="radio"
                                    name="address-label"
                                    :value="value"
                                    class="sr-only"
                                />
                                {{ text }}
                            </label>
                        </div>
                        <p
                            v-if="form.errors.label"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.label }}
                        </p>
                    </fieldset>

                    <div>
                        <label
                            for="addr-name"
                            class="text-xs font-medium text-slate-500"
                            >Full name</label
                        >
                        <input
                            id="addr-name"
                            v-model="form.recipient_name"
                            type="text"
                            autocomplete="name"
                            placeholder="Who receives the rider"
                            :class="inputClass"
                        />
                        <p
                            v-if="form.errors.recipient_name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.recipient_name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="addr-phone"
                            class="text-xs font-medium text-slate-500"
                            >Mobile number</label
                        >
                        <input
                            id="addr-phone"
                            v-model="form.phone"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="09XX XXX XXXX"
                            :class="inputClass"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            Pin your exact location
                        </p>
                        <div class="mt-1">
                            <AddressPinPicker
                                v-model="pin"
                                @address="onAddressFound"
                            />
                        </div>
                        <p
                            v-if="form.errors.latitude"
                            class="mt-1 text-xs text-red-600"
                            role="alert"
                        >
                            {{ form.errors.latitude }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="addr-address"
                            class="text-xs font-medium text-slate-500"
                            >Complete address</label
                        >
                        <input
                            id="addr-address"
                            v-model="form.address"
                            type="text"
                            autocomplete="street-address"
                            placeholder="House no., street, barangay, city"
                            :class="inputClass"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Add your house or unit number so the rider can find
                            you.
                        </p>
                        <p
                            v-if="form.errors.address"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <label
                        class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-slate-700"
                    >
                        <input
                            v-model="form.is_default"
                            type="checkbox"
                            class="size-4 accent-blue-600"
                            :disabled="
                                editingId !== null &&
                                addresses.find((a) => a.id === editingId)
                                    ?.is_default
                            "
                        />
                        Set as my default address
                    </label>

                    <button
                        type="submit"
                        class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />
                        Save address
                    </button>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete confirmation -->
        <AlertDialog
            :open="deleting !== null"
            @update:open="(open: boolean) => !open && (deleting = null)"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete this address?</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ deleting?.address }}
                        Your past orders keep the address they were placed with.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete"
                        >Delete</AlertDialogAction
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </UserLayout>
</template>
