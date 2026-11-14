<script setup lang="ts">
import { ADDRESS_LABELS, type SavedAddress } from '@/types/saved-address';
import { Link } from '@inertiajs/vue3';
import { Briefcase, Home, MapPin, PencilLine, Plus } from 'lucide-vue-next';

/**
 * Lets the customer choose which saved address an order uses, or type a different one.
 * `modelValue` is the chosen address id; null means "a different address".
 */
defineProps<{
    addresses: SavedAddress[];
    modelValue: string | null;
    /** Name used for the radio group so two pickers on one page stay separate. */
    name: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
    select: [address: SavedAddress | null];
}>();

function choose(address: SavedAddress | null) {
    emit('update:modelValue', address?.id ?? null);
    emit('select', address);
}

const icons = { home: Home, work: Briefcase, other: MapPin };
</script>

<template>
    <div role="radiogroup" aria-label="Choose an address" class="space-y-2">
        <label
            v-for="address in addresses"
            :key="address.id"
            class="flex min-h-14 cursor-pointer items-start gap-3 rounded-xl border p-3 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-blue-600"
            :class="
                modelValue === address.id
                    ? 'border-blue-600 bg-blue-50'
                    : 'border-slate-200 hover:border-slate-300'
            "
        >
            <input
                type="radio"
                :name="name"
                :value="address.id"
                :checked="modelValue === address.id"
                class="mt-1 size-4 shrink-0 accent-blue-600"
                @change="choose(address)"
            />
            <span class="min-w-0 flex-1">
                <span class="flex flex-wrap items-center gap-2">
                    <component
                        :is="icons[address.label]"
                        class="size-4 shrink-0 text-blue-600"
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
                </span>
                <span class="mt-0.5 block text-sm text-slate-700">
                    {{ address.recipient_name }} · {{ address.phone }}
                </span>
                <span class="block text-sm break-words text-slate-500">
                    {{ address.address }}
                </span>
            </span>
        </label>

        <label
            class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border p-3 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-blue-600"
            :class="
                modelValue === null
                    ? 'border-blue-600 bg-blue-50'
                    : 'border-slate-200 hover:border-slate-300'
            "
        >
            <input
                type="radio"
                :name="name"
                value=""
                :checked="modelValue === null"
                class="size-4 shrink-0 accent-blue-600"
                @change="choose(null)"
            />
            <PencilLine
                class="size-4 shrink-0 text-blue-600"
                aria-hidden="true"
            />
            <span class="text-sm font-semibold text-slate-900">
                Use a different address
            </span>
        </label>

        <Link
            href="/user/addresses"
            class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
        >
            <Plus class="size-4" aria-hidden="true" />
            Manage saved addresses
        </Link>
    </div>
</template>
