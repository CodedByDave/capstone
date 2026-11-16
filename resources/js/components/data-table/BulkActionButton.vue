<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useUndoToast } from '@/composables/useUndoToast';
import { router, usePage } from '@inertiajs/vue3';
import { Archive, ArchiveRestore } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

/**
 * "Archive selected" / "Restore selected" button for a DataTable's `#bulk-actions` slot. It posts
 * the selected row keys to the shop's bulk endpoint for `resource` straight away (these actions are
 * reversible, so there is no confirm dialog), clears the selection, and shows a toast with Undo that
 * runs the opposite action on the same rows for about 4.5 seconds.
 */
const props = defineProps<{
    /** Bulk resource name, e.g. `supplier` or `inventory-category`. */
    resource: string;
    action: 'archive' | 'restore';
    /** The selected row keys (the table's `v-model:selected`). */
    ids: Array<string | number>;
    /** Plural noun for the toast text, e.g. `suppliers`. */
    noun: string;
}>();

const emit = defineEmits<{ done: [] }>();

const page = usePage();
const { undoable } = useUndoToast();
const base = computed(() =>
    page.props.auth.user.role === 'owner' ? '/shop' : '/staff',
);
const isArchive = computed(() => props.action === 'archive');
const verb = computed(() => (isArchive.value ? 'Archive' : 'Restore'));

const processing = ref(false);

// `quiet` stops the server from flashing its own message: the Undo toast is the message here.
function undo(action: 'archive' | 'restore', ids: Array<string | number>) {
    router.post(
        `${base.value}/bulk/${props.resource}/${action}`,
        { ids, quiet: true },
        { preserveScroll: true, onSuccess: () => toast.success('Undone.') },
    );
}

function submit() {
    // Undo must act on the rows as they were selected, even after the selection is cleared.
    const ids = [...props.ids];
    const opposite = isArchive.value ? 'restore' : 'archive';
    processing.value = true;

    router.post(
        `${base.value}/bulk/${props.resource}/${props.action}`,
        { ids, quiet: true },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('done');
                undoable(
                    `${ids.length} ${props.noun} ${isArchive.value ? 'archived' : 'restored'}.`,
                    () => undo(opposite, ids),
                );
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Button
        type="button"
        size="sm"
        :variant="isArchive ? 'warning-outline' : 'success-outline'"
        :disabled="processing"
        @click="submit"
    >
        <component
            :is="isArchive ? Archive : ArchiveRestore"
            class="mr-1.5 h-4 w-4"
            aria-hidden="true"
        />
        {{ verb }} selected
    </Button>
</template>
