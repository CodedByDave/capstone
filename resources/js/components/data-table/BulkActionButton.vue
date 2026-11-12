<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { Archive, ArchiveRestore } from 'lucide-vue-next';
import { computed, ref } from 'vue';

/**
 * "Archive selected" / "Restore selected" button for a DataTable's `#bulk-actions` slot. It asks
 * for confirmation, posts the selected row keys to the shop's bulk endpoint for `resource`, and
 * clears the selection once the request succeeds.
 */
const props = defineProps<{
    /** Bulk resource name, e.g. `supplier` or `inventory-category`. */
    resource: string;
    action: 'archive' | 'restore';
    /** The selected row keys (the table's `v-model:selected`). */
    ids: Array<string | number>;
    /** Plural noun for the confirmation text, e.g. `suppliers`. */
    noun: string;
}>();

const emit = defineEmits<{ done: [] }>();

const page = usePage();
const base = computed(() =>
    page.props.auth.user.role === 'owner' ? '/shop' : '/staff',
);
const isArchive = computed(() => props.action === 'archive');
const verb = computed(() => (isArchive.value ? 'Archive' : 'Restore'));

const open = ref(false);
const processing = ref(false);

function submit() {
    processing.value = true;
    router.post(
        `${base.value}/bulk/${props.resource}/${props.action}`,
        { ids: props.ids },
        {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                emit('done');
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
        @click="open = true"
    >
        <component
            :is="isArchive ? Archive : ArchiveRestore"
            class="mr-1.5 h-4 w-4"
        />
        {{ verb }} selected
    </Button>

    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ verb }} {{ noun }}?</AlertDialogTitle>
                <AlertDialogDescription>
                    {{
                        isArchive
                            ? `Move the ${ids.length} selected ${noun} to the archive? You can restore them later.`
                            : `Restore the ${ids.length} selected ${noun}? They will return to the active list.`
                    }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <Button
                    variant="outline"
                    :disabled="processing"
                    @click="open = false"
                >
                    Cancel
                </Button>
                <Button
                    :variant="isArchive ? 'warning' : 'success'"
                    :disabled="processing"
                    @click="submit"
                >
                    {{ verb }}
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
