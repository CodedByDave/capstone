<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Package, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

export interface SupplyLine {
    inventory_id: number | '';
    quantity: number | string;
    unit: string;
}

export interface SupplyOption {
    id: number;
    name: string;
    unit: string;
    quantity: number;
}

const props = defineProps<{
    usesSupplies: boolean;
    supplies: SupplyLine[];
    options: SupplyOption[];
    perLabel: 'kg' | 'bundle' | 'piece';
    errors: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:usesSupplies': [value: boolean];
    'update:supplies': [value: SupplyLine[]];
}>();

const optionById = computed(
    () => new Map(props.options.map((option) => [option.id, option])),
);

function toggle() {
    const next = !props.usesSupplies;
    emit('update:usesSupplies', next);
    if (next && props.supplies.length === 0) addLine();
}

function addLine() {
    emit('update:supplies', [
        ...props.supplies,
        { inventory_id: '', quantity: 1, unit: '' },
    ]);
}

function removeLine(index: number) {
    emit(
        'update:supplies',
        props.supplies.filter((_, i) => i !== index),
    );
}

function update(index: number, patch: Partial<SupplyLine>) {
    emit(
        'update:supplies',
        props.supplies.map((line, i) =>
            i === index ? { ...line, ...patch } : line,
        ),
    );
}

function pick(index: number, id: number | '') {
    update(index, {
        inventory_id: id,
        unit: id === '' ? '' : (optionById.value.get(id)?.unit ?? ''),
    });
}

function lineError(index: number, field: string): string | undefined {
    return props.errors[`supplies.${index}.${field}`];
}
</script>

<template>
    <div id="service-supplies">
        <div class="mb-4 flex items-center justify-between gap-3">
            <p
                class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
            >
                Supplies
            </p>
            <button
                type="button"
                role="switch"
                :aria-checked="usesSupplies"
                aria-label="Uses supplies"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="usesSupplies ? 'bg-emerald-500' : 'bg-gray-300'"
                @click="toggle"
            >
                <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                    :class="usesSupplies ? 'translate-x-6' : 'translate-x-1'"
                />
            </button>
        </div>

        <p v-if="!usesSupplies" class="text-sm text-muted-foreground">
            Turn this on to list the inventory supplies this service uses. They
            are taken out of stock and recorded as an expense whenever an order
            for this service is placed.
        </p>

        <div v-else class="space-y-3">
            <p
                v-if="options.length === 0"
                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"
            >
                Your inventory has no active items yet. Add supplies in
                Inventory first.
            </p>

            <div
                v-for="(line, index) in supplies"
                :key="index"
                class="grid grid-cols-12 items-start gap-3 rounded-xl border p-3"
            >
                <div class="col-span-12 space-y-1 sm:col-span-5">
                    <label class="text-xs font-medium">Supply</label>
                    <select
                        :value="line.inventory_id"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        :class="{
                            'border-red-500': lineError(index, 'inventory_id'),
                        }"
                        @change="
                            pick(
                                index,
                                ($event.target as HTMLSelectElement).value ===
                                    ''
                                    ? ''
                                    : Number(
                                          ($event.target as HTMLSelectElement)
                                              .value,
                                      ),
                            )
                        "
                    >
                        <option value="">Select a supply…</option>
                        <option
                            v-for="option in options"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.name }} ({{ option.quantity }}
                            {{ option.unit }} in stock)
                        </option>
                    </select>
                    <p
                        v-if="lineError(index, 'inventory_id')"
                        class="text-xs text-red-500"
                    >
                        {{ lineError(index, 'inventory_id') }}
                    </p>
                </div>

                <div class="col-span-6 space-y-1 sm:col-span-3">
                    <label class="text-xs font-medium"
                        >Qty per {{ perLabel }}</label
                    >
                    <Input
                        :model-value="line.quantity"
                        type="number"
                        min="0.01"
                        step="0.01"
                        :class="{
                            'border-red-500': lineError(index, 'quantity'),
                        }"
                        @update:model-value="
                            update(index, { quantity: $event as string })
                        "
                    />
                    <p
                        v-if="lineError(index, 'quantity')"
                        class="text-xs text-red-500"
                    >
                        {{ lineError(index, 'quantity') }}
                    </p>
                </div>

                <div class="col-span-6 space-y-1 sm:col-span-3">
                    <label class="text-xs font-medium">Unit</label>
                    <Input
                        :model-value="line.unit"
                        maxlength="50"
                        placeholder="e.g. sachet"
                        :class="{ 'border-red-500': lineError(index, 'unit') }"
                        @update:model-value="
                            update(index, { unit: $event as string })
                        "
                    />
                    <p
                        v-if="lineError(index, 'unit')"
                        class="text-xs text-red-500"
                    >
                        {{ lineError(index, 'unit') }}
                    </p>
                </div>

                <div class="col-span-12 flex justify-end sm:col-span-1 sm:pt-5">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        :aria-label="`Remove supply ${index + 1}`"
                        @click="removeLine(index)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <p v-if="errors.supplies" class="text-xs text-red-500">
                {{ errors.supplies }}
            </p>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="options.length === 0"
                    @click="addLine"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Add supply
                </Button>
                <p
                    class="flex items-center gap-1.5 text-xs text-muted-foreground"
                >
                    <Package class="h-3.5 w-3.5" />
                    Stock is deducted in whole units, rounded up.
                </p>
            </div>
        </div>
    </div>
</template>
