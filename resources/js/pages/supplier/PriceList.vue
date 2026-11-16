<script setup lang="ts">
import { StatusPill } from '@/components/data-table';
import InputError from '@/components/InputError.vue';
import PriceListStatusPill, {
    type PriceListStatus,
} from '@/components/supplier/PriceListStatusPill.vue';
import { Button } from '@/components/ui/button';
import SupplierLayout from '@/layouts/supplier/SupplierLayout.vue';
import { compressPhoto } from '@/lib/compressPhoto';
import { router, useForm } from '@inertiajs/vue3';
import {
    FileImage,
    Loader2,
    RefreshCcw,
    ScanText,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface MatchResult {
    item: string;
    name: string;
    expected_price: number;
    found_price: number | null;
    result: 'matched' | 'price_mismatch' | 'not_found';
}

interface PriceList {
    public_id: string;
    status: PriceListStatus;
    photo_url: string;
    uploaded_at: string | null;
    checked_at: string | null;
    matched_count: number;
    item_count: number;
    results: MatchResult[];
}

defineProps<{
    supplier: { name: string; shop_name: string | null };
    counts: { total: number; in_stock: number; out_of_stock: number };
    priceLists: PriceList[];
    latestPriceList: PriceList | null;
}>();

const BASE = '/supplier-portal';

const peso = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});

/* ─── Price-list upload and OCR result ─────── */

const photoInput = ref<HTMLInputElement | null>(null);
const preparing = ref(false);
const uploadForm = useForm<{ photo: File | null }>({ photo: null });
const uploading = computed(() => preparing.value || uploadForm.processing);

async function onPhotoChosen(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';
    if (!file) return;

    preparing.value = true;
    try {
        // A smaller photo uploads faster and stays inside the OCR service's size limit.
        uploadForm.photo = await compressPhoto(file);
    } finally {
        preparing.value = false;
    }

    uploadForm.post(`${BASE}/price-lists`, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => uploadForm.reset(),
    });
}

const rechecking = ref(false);

function recheck(priceList: PriceList) {
    router.post(
        `${BASE}/price-lists/${priceList.public_id}/recheck`,
        {},
        {
            preserveScroll: true,
            onStart: () => (rechecking.value = true),
            onFinish: () => (rechecking.value = false),
        },
    );
}

const resultLabels: Record<MatchResult['result'], string> = {
    matched: 'Matches',
    price_mismatch: 'Different price',
    not_found: 'Not on photo',
};
const resultTones = {
    matched: 'success',
    price_mismatch: 'warning',
    not_found: 'danger',
} as const;

function formatDate(value: string | null) {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <SupplierLayout
        title="Price list"
        :supplier-name="supplier.name"
        :shop-name="supplier.shop_name"
    >
        <!-- Price list -->
        <section
            class="rounded-[14px] border bg-background p-4 sm:p-6"
            aria-labelledby="price-list-heading"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2
                        id="price-list-heading"
                        class="flex items-center gap-2 font-semibold"
                    >
                        <ScanText
                            class="h-4 w-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        Price list
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Upload a clear, well-lit photo of your printed price
                        list. OCR reads it and compares each supply's name and
                        price with what you entered.
                    </p>
                </div>
                <div>
                    <input
                        id="price-list-photo"
                        ref="photoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                        :disabled="uploading"
                        @change="onPhotoChosen"
                    />
                    <Button
                        :disabled="uploading || counts.total === 0"
                        @click="photoInput?.click()"
                    >
                        <Loader2
                            v-if="uploading"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <Upload v-else class="mr-1.5 h-4 w-4" />
                        {{ uploading ? 'Checking photo...' : 'Upload photo' }}
                    </Button>
                </div>
            </div>
            <InputError class="mt-2" :message="uploadForm.errors.photo" />
            <p
                v-if="counts.total === 0"
                class="mt-2 text-xs text-muted-foreground"
            >
                Add at least one supply first, so the price list has something
                to be checked against.
            </p>

            <div
                v-if="latestPriceList"
                class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]"
            >
                <a
                    :href="latestPriceList.photo_url"
                    target="_blank"
                    rel="noopener"
                    class="block overflow-hidden rounded-lg border bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <img
                        :src="latestPriceList.photo_url"
                        alt="Latest uploaded price list"
                        class="max-h-80 w-full object-contain"
                    />
                </a>
                <div class="min-w-0 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <PriceListStatusPill :status="latestPriceList.status" />
                        <span class="text-sm text-muted-foreground">
                            {{ latestPriceList.matched_count }} of
                            {{ latestPriceList.item_count }} supplies match ·
                            checked {{ formatDate(latestPriceList.checked_at) }}
                        </span>
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="rechecking"
                            @click="recheck(latestPriceList)"
                        >
                            <Loader2
                                v-if="rechecking"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            <RefreshCcw v-else class="mr-1.5 h-4 w-4" />
                            Re-check
                        </Button>
                    </div>
                    <p
                        v-if="latestPriceList.status === 'unreadable'"
                        class="text-sm text-muted-foreground"
                    >
                        The photo could not be read. Upload a sharper photo
                        taken straight on, with the whole list in frame.
                    </p>
                    <div v-else class="overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm">
                            <caption class="sr-only">
                                Price list check per supply
                            </caption>
                            <thead class="bg-muted/50">
                                <tr
                                    class="text-left text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    <th scope="col" class="px-3 py-2">
                                        Supply
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-2 text-right"
                                    >
                                        Your price
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-2 text-right"
                                    >
                                        On photo
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Result
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="row in latestPriceList.results"
                                    :key="row.item"
                                >
                                    <td class="px-3 py-2 font-medium">
                                        {{ row.name }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right tabular-nums"
                                    >
                                        {{ peso.format(row.expected_price) }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right tabular-nums"
                                    >
                                        {{
                                            row.found_price === null
                                                ? '—'
                                                : peso.format(row.found_price)
                                        }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <StatusPill
                                            :tone="resultTones[row.result]"
                                        >
                                            {{ resultLabels[row.result] }}
                                        </StatusPill>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="mt-6 flex flex-col items-center gap-2 rounded-lg border border-dashed py-10 text-center text-sm text-muted-foreground"
            >
                <FileImage class="h-8 w-8" aria-hidden="true" />
                No price list uploaded yet.
            </div>

            <div v-if="priceLists.length > 1" class="mt-6">
                <h3 class="mb-2 text-sm font-medium">Earlier uploads</h3>
                <ul class="divide-y rounded-lg border">
                    <li
                        v-for="list in priceLists.slice(1)"
                        :key="list.public_id"
                        class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 text-sm"
                    >
                        <a
                            :href="list.photo_url"
                            target="_blank"
                            rel="noopener"
                            class="flex items-center gap-2 hover:underline"
                        >
                            <FileImage
                                class="h-4 w-4 text-muted-foreground"
                                aria-hidden="true"
                            />
                            {{ formatDate(list.uploaded_at) }}
                        </a>
                        <PriceListStatusPill :status="list.status" />
                    </li>
                </ul>
            </div>
        </section>
    </SupplierLayout>
</template>
