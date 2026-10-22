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
import { postpone } from '@/lib/ratingPrompt';
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

/** The "How was your laundry?" popup a customer sees once an order is done. */
const props = defineProps<{
    paymentPublicId: string;
    orderNumber: string;
    shopName: string;
}>();

const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ rated: [] }>();

const form = useForm({ rating: 0, comment: '' });

function submit() {
    form.post(`/user/orders/${props.paymentPublicId}/rating`, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.toast as
                | { type: string; message: string }
                | undefined;

            if (flash?.type === 'error') {
                form.setError('rating', flash.message);
                return;
            }

            open.value = false;
            form.reset();
            emit('rated');
        },
    });
}

function later() {
    postpone(props.paymentPublicId);
    open.value = false;
}

// Closing with the X or Escape counts as "maybe later".
function onOpenChange(value: boolean) {
    if (!value && !form.recentlySuccessful) postpone(props.paymentPublicId);
    open.value = value;
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>How was your laundry?</DialogTitle>
                <DialogDescription>
                    Rate order #{{ orderNumber }} at {{ shopName }}. It takes a
                    few seconds and helps other customers.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <StarRatingInput
                    v-model="form.rating"
                    label="Rate your order from 1 to 5 stars"
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
                        for="order-rating-comment"
                        class="text-sm font-medium"
                    >
                        Comment
                        <span class="font-normal text-muted-foreground"
                            >(optional)</span
                        >
                    </label>
                    <Textarea
                        id="order-rating-comment"
                        v-model="form.comment"
                        rows="3"
                        maxlength="500"
                        placeholder="Tell the shop what went well or what to improve"
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
                <p class="text-xs text-muted-foreground">
                    Only the shop can read your comment. Your rating can't be
                    changed once sent.
                </p>
            </div>

            <DialogFooter class="gap-2 sm:gap-2">
                <Button variant="outline" @click="later">Maybe later</Button>
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
