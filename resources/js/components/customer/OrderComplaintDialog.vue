<script setup lang="ts">
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
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Loader2, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

/** "Report a problem" popup: what went wrong, a description, and up to three photos. */
const props = defineProps<{
    paymentPublicId: string;
    orderNumber: string;
    shopName: string;
    types: Record<string, string>;
}>();

const open = defineModel<boolean>('open', { default: false });

const MAX_PHOTOS = 3;
const MAX_BYTES = 5 * 1024 * 1024;

const form = useForm<{ type: string; description: string; photos: File[] }>({
    type: '',
    description: '',
    photos: [],
});

const fileInput = ref<HTMLInputElement | null>(null);
const photoError = ref('');
const previews = computed(() =>
    form.photos.map((file) => ({ file, url: URL.createObjectURL(file) })),
);

function revokePreviews() {
    previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
}
onBeforeUnmount(revokePreviews);

function addPhotos(event: Event) {
    const input = event.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);
    input.value = '';
    photoError.value = '';

    for (const file of picked) {
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            photoError.value = 'Photos must be JPG, PNG or WebP images.';
            continue;
        }
        if (file.size > MAX_BYTES) {
            photoError.value = 'Each photo must be 5 MB or smaller.';
            continue;
        }
        if (form.photos.length >= MAX_PHOTOS) {
            photoError.value = `You can attach up to ${MAX_PHOTOS} photos.`;
            break;
        }
        form.photos.push(file);
    }
}

function removePhoto(index: number) {
    revokePreviews();
    form.photos.splice(index, 1);
    photoError.value = '';
}

function submit() {
    form.post(`/user/orders/${props.paymentPublicId}/complaints`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.toast as
                | { type: string; message: string }
                | undefined;

            if (flash?.type === 'error') {
                form.setError('type', flash.message);
                return;
            }

            revokePreviews();
            open.value = false;
            form.reset();
        },
    });
}

const canSubmit = computed(
    () =>
        !form.processing &&
        form.type !== '' &&
        form.description.trim().length >= 10,
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90vh] max-w-md overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Report a problem</DialogTitle>
                <DialogDescription>
                    Tell {{ shopName }} what went wrong with order #{{
                        orderNumber
                    }}. Adding photos helps them resolve it faster.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <div class="space-y-1.5">
                    <label for="complaint-type" class="text-sm font-medium"
                        >What went wrong?</label
                    >
                    <select
                        id="complaint-type"
                        v-model="form.type"
                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                    >
                        <option value="" disabled>Choose an issue</option>
                        <option
                            v-for="(label, value) in types"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <p
                        v-if="form.errors.type"
                        class="text-xs text-red-500"
                        role="alert"
                    >
                        {{ form.errors.type }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label
                        for="complaint-description"
                        class="text-sm font-medium"
                        >Describe the problem</label
                    >
                    <Textarea
                        id="complaint-description"
                        v-model="form.description"
                        rows="4"
                        maxlength="1000"
                        placeholder="e.g. My white t-shirt came back with a yellow stain on the sleeve."
                    />
                    <div class="flex justify-between text-xs">
                        <span class="text-red-500" role="alert">{{
                            form.errors.description
                        }}</span>
                        <span class="text-muted-foreground"
                            >{{ form.description.length }}/1000</span
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium">
                        Photos
                        <span class="font-normal text-muted-foreground"
                            >(optional, up to {{ MAX_PHOTOS }})</span
                        >
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="(preview, index) in previews"
                            :key="preview.url"
                            class="relative h-20 w-20 overflow-hidden rounded-lg border"
                        >
                            <img
                                :src="preview.url"
                                alt="Selected photo preview"
                                class="h-full w-full object-cover"
                            />
                            <button
                                type="button"
                                class="absolute top-1 right-1 rounded-full bg-black/60 p-0.5 text-white hover:bg-black/80"
                                aria-label="Remove photo"
                                @click="removePhoto(index)"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <button
                            v-if="form.photos.length < MAX_PHOTOS"
                            type="button"
                            class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded-lg border border-dashed text-xs text-muted-foreground hover:bg-muted"
                            @click="fileInput?.click()"
                        >
                            <ImagePlus class="h-5 w-5" aria-hidden="true" />
                            Add photo
                        </button>
                    </div>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        class="hidden"
                        @change="addPhotos"
                    />
                    <p
                        v-if="photoError || form.errors.photos"
                        class="text-xs text-red-500"
                        role="alert"
                    >
                        {{ photoError || form.errors.photos }}
                    </p>
                    <p
                        v-for="(message, key) in form.errors"
                        v-show="String(key).startsWith('photos.')"
                        :key="key"
                        class="text-xs text-red-500"
                        role="alert"
                    >
                        {{ message }}
                    </p>
                </div>

                <p class="text-xs text-muted-foreground">
                    Only you and the shop can see your report and photos.
                </p>
            </div>

            <DialogFooter class="gap-2 sm:gap-2">
                <Button variant="outline" @click="open = false">Cancel</Button>
                <Button :disabled="!canSubmit" @click="submit">
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ form.processing ? 'Sending…' : 'Send report' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
