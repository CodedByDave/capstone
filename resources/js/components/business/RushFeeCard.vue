<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { Loader2, Zap } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    rushFee: number;
}>();

const form = useForm({
    rush_fee: props.rushFee > 0 ? String(props.rushFee) : '',
});

function save() {
    form.patch('/shop/settings/rush-fee', {
        preserveScroll: true,
        preserveState: (page) =>
            Object.keys(page.props.errors ?? {}).length > 0,
        onError: () => toast.error('The rush fee was not saved.'),
    });
}
</script>

<template>
    <section class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <Zap class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">Rush Fee</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Orders are worked on first come, first served. Staff can mark a
                waiting order as rush to move it to the front of the queue. This
                flat fee is then added to the order total. Leave it empty or ₱0
                to turn rush off.
            </p>
        </div>

        <div class="max-w-xs space-y-2">
            <label for="rush-fee" class="text-sm font-medium">
                Rush fee (₱)
            </label>
            <Input
                id="rush-fee"
                v-model="form.rush_fee"
                type="number"
                min="0"
                max="5000"
                step="0.01"
                inputmode="decimal"
                placeholder="0.00"
            />
            <InputError :message="form.errors.rush_fee" />
        </div>

        <Button :disabled="form.processing" class="min-h-11" @click="save">
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save rush fee
        </Button>
    </section>
</template>
