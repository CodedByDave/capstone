<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { SettingsSection } from '@/composables/useSettingsSaveBar';
import { Loader2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    dirty: SettingsSection[];
    saving: boolean;
}>();

defineEmits<{ save: []; discard: [] }>();

const summary = computed(() => {
    const count = props.dirty.length;

    return `${count} unsaved ${count === 1 ? 'change' : 'changes'}`;
});
const names = computed(() =>
    props.dirty.map((section) => section.label).join(', '),
);
</script>

<template>
    <div
        v-if="dirty.length > 0"
        class="sticky bottom-4 z-20 flex flex-wrap items-center gap-3 rounded-xl border bg-background/95 px-4 py-3 shadow-lg backdrop-blur"
        role="region"
        aria-label="Unsaved settings"
    >
        <div class="min-w-0 flex-1" aria-live="polite">
            <p class="text-sm font-semibold">{{ summary }}</p>
            <p class="truncate text-xs text-muted-foreground">{{ names }}</p>
        </div>
        <Button
            variant="outline"
            class="min-h-11"
            :disabled="saving"
            @click="$emit('discard')"
        >
            Discard
        </Button>
        <Button class="min-h-11" :disabled="saving" @click="$emit('save')">
            <Loader2 v-if="saving" class="mr-1.5 h-4 w-4 animate-spin" />
            {{ saving ? 'Saving…' : 'Save all changes' }}
        </Button>
    </div>
</template>
