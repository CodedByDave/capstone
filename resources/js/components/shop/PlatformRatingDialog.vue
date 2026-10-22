<script setup lang="ts">
import StarRatingInput from '@/components/customer/StarRatingInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import type { AppPageProps } from '@/types';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';

/**
 * Asks the shop owner to rate LaundryHub itself. The server decides when (after the shop completes
 * an order, at most once a month) and shares `platformRatingPrompt`; closing the popup in any way
 * other than sending a rating tells the server so it does not ask again for a month.
 */
const page = usePage<AppPageProps>();
const open = ref(false);
const form = useForm({ rating: 0, comment: '' });
let timer: ReturnType<typeof setTimeout> | undefined;

onMounted(() => {
    if (!page.props.platformRatingPrompt) return;

    // Let the page settle first; a popup the instant a page opens feels like an ambush.
    timer = setTimeout(() => (open.value = true), 1500);
});

onBeforeUnmount(() => clearTimeout(timer));

function submit() {
    form.post('/shop/platform-rating', {
        preserveScroll: true,
        onSuccess: (response) => {
            // Shop pages only read the flash message when they first load, so show it here.
            const flash = response.props.toast as
                | { type: string; message: string }
                | undefined;

            if (flash?.type === 'error') {
                form.setError('rating', flash.message);
                return;
            }

            if (flash?.message) toast.success(flash.message);
            open.value = false;
            form.reset();
        },
    });
}

function dismiss() {
    open.value = false;
    router.post(
        '/shop/platform-rating/dismiss',
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['platformRatingPrompt'],
        },
    );
}

function onOpenChange(value: boolean) {
    if (!value) dismiss();
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>How is LaundryHub working for you?</DialogTitle>
                <DialogDescription>
                    You just completed an order. Rate the platform to help us
                    improve. We ask at most once a month.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <StarRatingInput
                    v-model="form.rating"
                    label="Rate LaundryHub from 1 to 5 stars"
                />
                <p
                    v-if="form.errors.rating"
                    class="text-center text-xs text-red-500"
                    role="alert"
                >
                    {{ form.errors.rating }}
                </p>

                <div class="space-y-1.5">
                    <label
                        for="platform-rating-comment"
                        class="text-sm font-medium"
                    >
                        Comment
                        <span class="font-normal text-muted-foreground"
                            >(optional)</span
                        >
                    </label>
                    <Textarea
                        id="platform-rating-comment"
                        v-model="form.comment"
                        rows="3"
                        maxlength="500"
                        placeholder="What works well, and what should we fix?"
                    />
                    <div class="flex justify-between text-xs">
                        <span class="text-red-500" role="alert">{{
                            form.errors.comment
                        }}</span>
                        <span class="text-muted-foreground"
                            >{{ form.comment.length }}/500</span
                        >
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2 sm:gap-2">
                <Button variant="outline" @click="dismiss">Maybe later</Button>
                <Button
                    :disabled="form.processing || form.rating === 0"
                    @click="submit"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ form.processing ? 'Sending…' : 'Send rating' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
