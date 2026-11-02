<script setup lang="ts">
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

export interface EditableService {
    id: number;
    service_name: string;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    price_per_piece: string | null;
}

export interface ServiceLineInput {
    service_id: string | number;
    weight_kg: string | number;
    piece_count: string | number;
}

const props = defineProps<{
    services: EditableService[];
    errors?: Record<string, string>;
}>();

const lines = defineModel<ServiceLineInput[]>({ required: true });

const money = (v: number) =>
    `₱${v.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const serviceOf = (line: ServiceLineInput) =>
    props.services.find((s) => s.id === Number(line.service_id));

const label = (svc: EditableService) => {
    if (svc.pricing_model === 'per_kg')
        return `${svc.service_name} — ${money(Number(svc.price_per_kg))}/kg`;
    if (svc.pricing_model === 'per_piece')
        return `${svc.service_name} — ${money(Number(svc.price_per_piece))}/piece`;
    return `${svc.service_name} — ${money(Number(svc.bundle_price))} / ${svc.bundle_weight_kg}kg bundle`;
};

/** Mirrors the server's pricing; the server recomputes the real amount when the order is saved. */
const amountOf = (line: ServiceLineInput) => {
    const svc = serviceOf(line);
    if (!svc) return 0;
    if (svc.pricing_model === 'per_piece')
        return (
            (parseInt(String(line.piece_count)) || 0) *
            Number(svc.price_per_piece)
        );
    const weight = parseFloat(String(line.weight_kg)) || 0;
    if (svc.pricing_model === 'per_bundle') {
        if (weight <= 0) return 0;
        return (
            Math.max(
                1,
                Math.ceil(
                    weight / Math.max(Number(svc.bundle_weight_kg), 0.01),
                ),
            ) * Number(svc.bundle_price)
        );
    }
    return weight * Number(svc.price_per_kg);
};

const total = computed(() =>
    lines.value.reduce((sum, line) => sum + amountOf(line), 0),
);
defineExpose({ total });

const add = () =>
    lines.value.push({ service_id: '', weight_kg: '', piece_count: '' });
const remove = (index: number) => lines.value.splice(index, 1);

const onServiceChange = (line: ServiceLineInput) => {
    const svc = serviceOf(line);
    line.weight_kg = '';
    line.piece_count = svc?.pricing_model === 'per_piece' ? 1 : '';
};

const error = (index: number, field: string) =>
    props.errors?.[`service_lines.${index}.${field}`];

const blurOnWheel = (event: WheelEvent) =>
    (event.target as HTMLInputElement).blur();
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Additional services
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Add another service for the same customer, for example a
                    comforter priced per piece.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex min-h-[44px] items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                @click="add"
            >
                <Plus class="h-4 w-4" aria-hidden="true" />
                Add another service
            </button>
        </div>

        <div
            v-for="(line, index) in lines"
            :key="index"
            class="grid grid-cols-12 items-start gap-x-4 gap-y-3 rounded-lg border border-gray-200 p-4"
        >
            <div class="col-span-12 space-y-1 sm:col-span-6">
                <label
                    class="text-sm font-medium"
                    :for="`line-service-${index}`"
                    >Service <span class="text-red-500">*</span></label
                >
                <select
                    :id="`line-service-${index}`"
                    v-model="line.service_id"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    :class="{ 'border-red-500': error(index, 'service_id') }"
                    @change="onServiceChange(line)"
                >
                    <option value="" disabled>Select a service…</option>
                    <option
                        v-for="svc in services"
                        :key="svc.id"
                        :value="svc.id"
                    >
                        {{ label(svc) }}
                    </option>
                </select>
                <p
                    v-if="error(index, 'service_id')"
                    class="text-xs text-red-500"
                >
                    {{ error(index, 'service_id') }}
                </p>
            </div>

            <div class="col-span-8 space-y-1 sm:col-span-4">
                <template v-if="serviceOf(line)?.pricing_model === 'per_piece'">
                    <label
                        class="text-sm font-medium"
                        :for="`line-pieces-${index}`"
                        >Number of pieces
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        :id="`line-pieces-${index}`"
                        v-model="line.piece_count"
                        type="number"
                        min="1"
                        max="500"
                        step="1"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :class="{
                            'border-red-500': error(index, 'piece_count'),
                        }"
                        @wheel="blurOnWheel"
                    />
                    <p
                        v-if="error(index, 'piece_count')"
                        class="text-xs text-red-500"
                    >
                        {{ error(index, 'piece_count') }}
                    </p>
                </template>
                <template v-else>
                    <label
                        class="text-sm font-medium"
                        :for="`line-weight-${index}`"
                        >Weight (kg)</label
                    >
                    <input
                        :id="`line-weight-${index}`"
                        v-model="line.weight_kg"
                        type="number"
                        min="0.1"
                        step="0.01"
                        :disabled="!serviceOf(line)"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-muted/40"
                        :class="{ 'border-red-500': error(index, 'weight_kg') }"
                        @wheel="blurOnWheel"
                    />
                    <p
                        v-if="error(index, 'weight_kg')"
                        class="text-xs text-red-500"
                    >
                        {{ error(index, 'weight_kg') }}
                    </p>
                </template>
            </div>

            <div
                class="col-span-4 flex items-end justify-end gap-2 sm:col-span-2"
            >
                <span
                    class="pb-2.5 text-sm font-semibold whitespace-nowrap tabular-nums"
                    >{{ money(amountOf(line)) }}</span
                >
                <button
                    type="button"
                    class="inline-flex h-[44px] w-[44px] items-center justify-center rounded-lg text-gray-500 transition hover:bg-red-50 hover:text-red-600 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :aria-label="`Remove additional service ${index + 1}`"
                    @click="remove(index)"
                >
                    <Trash2 class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </div>
    </div>
</template>
