<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { serviceCategories } from '@/constants/serviceCategories';
import { router } from '@inertiajs/vue3';
import { Loader2, Plus } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

interface CategoryOption {
    value: string;
    label: string;
    description: string | null;
}

const props = defineProps<{
    modelValue: string;
    customCategories: CategoryOption[];
    base: string;
    error?: string;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const selected = computed(
    () =>
        serviceCategories.find((c) => c.value === props.modelValue) ??
        props.customCategories.find((c) => c.value === props.modelValue),
);

const adding = ref(false);
const saving = ref(false);
const name = ref('');
const description = ref('');
const addError = ref('');

function openAdd() {
    adding.value = true;
    name.value = '';
    description.value = '';
    addError.value = '';
}

function addCategory() {
    if (saving.value) return;
    saving.value = true;
    addError.value = '';
    const typed = name.value.trim();

    router.post(
        `${props.base}/categories`,
        { name: typed, description: description.value || null },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: async () => {
                await nextTick();
                const created = props.customCategories.find(
                    (c) => c.label.toLowerCase() === typed.toLowerCase(),
                );
                if (created) emit('update:modelValue', created.value);
                adding.value = false;
            },
            onError: (errors) => {
                addError.value = errors.name ?? 'The category was not saved.';
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="space-y-1">
        <label class="text-sm font-medium">
            Service Category
            <span class="text-red-500">*</span>
        </label>
        <div class="flex gap-2">
            <select
                :value="modelValue"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                :class="{ 'border-red-500': error }"
                @change="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLSelectElement).value,
                    )
                "
            >
                <optgroup label="Standard categories">
                    <option
                        v-for="category in serviceCategories"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </option>
                </optgroup>
                <optgroup
                    v-if="customCategories.length"
                    label="Your categories"
                >
                    <option
                        v-for="category in customCategories"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </option>
                </optgroup>
            </select>
            <Button
                type="button"
                variant="outline"
                class="shrink-0"
                @click="openAdd"
            >
                <Plus class="mr-1.5 h-4 w-4" /> New category
            </Button>
        </div>
        <p class="text-xs leading-5 text-muted-foreground">
            {{ selected?.description }}
        </p>
        <p v-if="error" class="text-xs text-red-500">{{ error }}</p>

        <div
            v-if="adding"
            class="mt-2 space-y-3 rounded-xl border bg-muted/30 p-4"
        >
            <p class="text-sm font-semibold">Add your own category</p>
            <div class="space-y-1">
                <label class="text-xs font-medium" for="new-category-name"
                    >Category name</label
                >
                <Input
                    id="new-category-name"
                    v-model="name"
                    maxlength="80"
                    placeholder="e.g. Shoe Cleaning"
                    :class="{ 'border-red-500': addError }"
                    @keydown.enter.prevent="addCategory"
                />
                <p v-if="addError" class="text-xs text-red-500">
                    {{ addError }}
                </p>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium" for="new-category-desc"
                    >Description (optional)</label
                >
                <Input
                    id="new-category-desc"
                    v-model="description"
                    maxlength="500"
                    placeholder="What kind of services belong here?"
                />
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="saving"
                    @click="adding = false"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    size="sm"
                    :disabled="saving || name.trim().length < 2"
                    @click="addCategory"
                >
                    <Loader2
                        v-if="saving"
                        class="mr-1.5 h-4 w-4 animate-spin"
                    />
                    Add category
                </Button>
            </div>
        </div>
    </div>
</template>
