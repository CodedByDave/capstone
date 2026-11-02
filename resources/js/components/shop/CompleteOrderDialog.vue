<script setup lang="ts">
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

withDefaults(
    defineProps<{
        orderNumber?: string;
        customerName?: string;
        /** `complete` marks the order completed; `revert` takes a mistaken completion back. */
        mode?: 'complete' | 'revert';
    }>(),
    { mode: 'complete' },
);

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ confirm: [] }>();
</script>

<template>
    <AlertDialog :open="open" @update:open="open = $event">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{
                    mode === 'complete'
                        ? 'Mark order as completed'
                        : 'Revert to in progress'
                }}</AlertDialogTitle>
                <AlertDialogDescription>
                    <template v-if="mode === 'complete'">
                        Mark order
                        <span class="font-semibold text-foreground">{{
                            orderNumber
                        }}</span>
                        for
                        <span class="font-semibold text-foreground">{{
                            customerName
                        }}</span>
                        as completed now?
                    </template>
                    <template v-else>
                        Move order
                        <span class="font-semibold text-foreground">{{
                            orderNumber
                        }}</span>
                        for
                        <span class="font-semibold text-foreground">{{
                            customerName
                        }}</span>
                        back to in progress?
                    </template>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="open = false"
                    >Cancel</AlertDialogCancel
                >
                <AlertDialogAction
                    @click="
                        emit('confirm');
                        open = false;
                    "
                >
                    {{ mode === 'complete' ? 'Mark as completed' : 'Revert' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
