<script setup lang="ts">
import DeliveryPricingCard, {
    type DeliveryPricing,
} from '@/components/business/DeliveryPricingCard.vue';
import OperatingHoursCard from '@/components/business/OperatingHoursCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Building2,
    CheckCircle2,
    Database,
    Download,
    ExternalLink,
    ImagePlus,
    Loader2,
    MapPin,
    Navigation,
    SlidersHorizontal,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shop: {
        shop_name: string;
        phone: string | null;
        block_street: string | null;
        municipality: string | null;
        barangay: string | null;
        postal_code: string | null;
        latitude: number | null;
        longitude: number | null;
        status: string;
        cover_photo: string | null;
    };
    deliveryPricing: DeliveryPricing;
    operatingHours: {
        is_open_24_7: boolean;
        opens_at: string;
        closes_at: string;
    };
}>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
onMounted(() => {
    const flash = (page.props as any).toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'success') toast.success(flash.message);
    else if (flash.type === 'error') toast.error(flash.message);
    else toast(flash.message);
});

// ─── Cover photo ──────────────────────────────────────────────────────────────

const coverFile = ref<File | null>(null);
const coverPreview = ref<string | null>(props.shop.cover_photo);
const uploadingCover = ref(false);
const removingCover = ref(false);
const coverInput = ref<HTMLInputElement | null>(null);

function onCoverChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    coverFile.value = file;
    coverPreview.value = URL.createObjectURL(file);
}

function uploadCover() {
    if (!coverFile.value) return;
    uploadingCover.value = true;
    const data = new FormData();
    data.append('cover_photo', coverFile.value);
    router.post('/shop/settings/cover-photo', data, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            coverFile.value = null;
            toast.success('Cover photo saved successfully.');
        },
        onError: () => {
            toast.error('Failed to save cover photo. Please try again.');
        },
        onFinish: () => {
            uploadingCover.value = false;
        },
    });
}

function removeCover() {
    removingCover.value = true;
    router.post(
        '/shop/settings/cover-photo/clear',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                coverFile.value = null;
                coverPreview.value = null;
                toast.success('Cover photo removed.');
            },
            onError: () => {
                toast.error('Failed to remove cover photo. Please try again.');
            },
            onFinish: () => {
                removingCover.value = false;
            },
        },
    );
}

// ─── Geo form ─────────────────────────────────────────────────────────────────

const lat = ref<string>(props.shop.latitude?.toString() ?? '');
const lng = ref<string>(props.shop.longitude?.toString() ?? '');
const locating = ref(false);
const saving = ref(false);
const clearing = ref(false);

const hasGeo = computed(
    () => props.shop.latitude !== null && props.shop.longitude !== null,
);
const geoDirty = computed(
    () =>
        lat.value !== (props.shop.latitude?.toString() ?? '') ||
        lng.value !== (props.shop.longitude?.toString() ?? ''),
);
const addressLine = computed(
    () =>
        [
            props.shop.block_street,
            props.shop.barangay,
            props.shop.municipality,
            props.shop.postal_code,
        ]
            .filter(Boolean)
            .join(', ') || '—',
);
const sectionLinks = computed(() => [
    { href: '#hours', label: 'Operating Hours' },
    { href: '#delivery', label: 'Delivery Pricing' },
    { href: '#cover', label: 'Cover Photo' },
    { href: '#location', label: 'Shop Location' },
    { href: '#data', label: 'Your Data' },
]);

const mapsUrl = computed(() => {
    if (!hasGeo.value) return null;
    return `https://www.google.com/maps?q=${props.shop.latitude},${props.shop.longitude}`;
});

function useMyLocation() {
    if (!navigator.geolocation) {
        toast.error('Geolocation is not supported by your browser.');
        return;
    }
    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            lat.value = pos.coords.latitude.toFixed(7);
            lng.value = pos.coords.longitude.toFixed(7);
            locating.value = false;
            toast.success('Location detected. Click Save to confirm.');
        },
        () => {
            locating.value = false;
            toast.error(
                'Could not get your location. Please allow location access and try again.',
            );
        },
        { timeout: 8_000 },
    );
}

function saveGeo() {
    const parsedLat = lat.value ? parseFloat(lat.value) : null;
    const parsedLng = lng.value ? parseFloat(lng.value) : null;

    if ((lat.value && isNaN(parsedLat!)) || (lng.value && isNaN(parsedLng!))) {
        toast.error('Please enter valid coordinates.');
        return;
    }

    saving.value = true;
    router.post(
        '/shop/settings/geo',
        {
            latitude: parsedLat,
            longitude: parsedLng,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

function clearGeo() {
    clearing.value = true;
    router.post(
        '/shop/settings/geo/clear',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                lat.value = '';
                lng.value = '';
            },
            onFinish: () => {
                clearing.value = false;
            },
        },
    );
}
</script>

<template>

    <Head title="Shop Settings" />

    <ShopLayout title="Shop Settings">
        <div class="mx-auto max-w-5xl space-y-6 px-6">
            <!-- ── Header ── -->
            <div>
                <h2 class="text-lg font-semibold">Shop Settings</h2>
                <p class="text-sm text-muted-foreground">
                    Manage your shop profile, hours, delivery and location.
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
                <!-- ── Sections ── -->
                <div class="min-w-0 space-y-8">
                    <!-- Operating Hours -->
                    <div id="hours" class="scroll-mt-4">
                        <OperatingHoursCard :hours="props.operatingHours" />
                    </div>

                    <!-- Delivery Pricing -->
                    <div id="delivery" class="scroll-mt-4 border-t pt-8">
                        <DeliveryPricingCard :pricing="props.deliveryPricing" />
                    </div>

                    <!-- Cover Photo -->
                    <section id="cover" class="scroll-mt-4 space-y-5 border-t pt-8">
                        <div>
                            <div class="flex items-center gap-2">
                                <ImagePlus class="h-4 w-4 text-muted-foreground" />
                                <h3 class="text-base font-semibold">
                                    Cover Photo
                                </h3>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                This photo appears on your shop card in the user
                                dashboard and shop listing.
                            </p>
                        </div>

                        <div
                            class="group relative flex h-48 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-muted/30">
                            <img v-if="coverPreview" :src="coverPreview" alt="Cover photo"
                                class="h-full w-full object-cover" />
                            <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                                <ImagePlus class="h-10 w-10 opacity-30" />
                                <p class="text-xs">No cover photo uploaded</p>
                            </div>

                            <button v-if="coverPreview" type="button" aria-label="Remove cover photo"
                                class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow transition group-hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :disabled="removingCover" @click="removeCover">
                                <Loader2 v-if="removingCover" class="h-4 w-4 animate-spin" />
                                <Trash2 v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <input ref="coverInput" type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                        <p class="text-xs text-muted-foreground">
                            PNG, JPG or WEBP — max 4 MB.
                        </p>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <p class="text-xs text-muted-foreground" aria-live="polite">
                                {{
                                    coverFile
                                        ? 'You have unsaved changes'
                                        : 'No changes yet'
                                }}
                            </p>
                            <Button variant="outline" @click="coverInput?.click()">
                                <Upload class="mr-1.5 h-4 w-4" />
                                {{
                                    coverPreview
                                        ? 'Replace Photo'
                                        : 'Upload Photo'
                                }}
                            </Button>
                            <Button :disabled="!coverFile || uploadingCover" @click="uploadCover">
                                <Loader2 v-if="uploadingCover" class="mr-1.5 h-4 w-4 animate-spin" />
                                {{ uploadingCover ? 'Saving…' : 'Save Photo' }}
                            </Button>
                        </div>
                    </section>

                    <!-- Geolocation -->
                    <section id="location" class="scroll-mt-4 space-y-5 border-t pt-8">
                        <div>
                            <div class="flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                <h3 class="text-base font-semibold">
                                    Shop Location (GPS)
                                </h3>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Set your shop's GPS coordinates to enable
                                geolocation-based attendance. Staff must be
                                within 500 m to clock in; otherwise they are
                                auto-marked absent.
                            </p>
                        </div>

                        <div v-if="hasGeo"
                            class="flex items-center justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600" />
                                <div>
                                    <p class="text-xs font-semibold text-emerald-700">
                                        Location is set
                                    </p>
                                    <p class="font-mono text-xs text-emerald-700">
                                        {{ props.shop.latitude?.toFixed(6) }},
                                        {{ props.shop.longitude?.toFixed(6) }}
                                    </p>
                                </div>
                            </div>
                            <a v-if="mapsUrl" :href="mapsUrl" target="_blank" rel="noopener"
                                class="flex items-center gap-1 text-xs font-medium text-emerald-700 hover:underline">
                                View on Maps
                                <ExternalLink class="h-3 w-3" />
                            </a>
                        </div>
                        <div v-else class="flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3">
                            <MapPin class="h-4 w-4 shrink-0 text-amber-600" />
                            <p class="text-xs text-amber-700">
                                No location set. Staff clock-in will not check
                                distance.
                            </p>
                        </div>

                        <div v-if="props.shop.status !== 'active'"
                            class="flex items-start gap-2 rounded-xl bg-amber-50 px-4 py-3">
                            <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                            <p class="text-xs text-amber-700">
                                Your shop is currently
                                <span class="font-semibold capitalize">{{
                                    props.shop.status
                                    }}</span>
                                and will not appear in user searches until it is
                                approved and active.
                            </p>
                        </div>

                        <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="shop-latitude" class="text-sm font-medium">Latitude</label>
                                <Input id="shop-latitude" v-model="lat" placeholder="e.g. 14.5995"
                                    class="font-mono text-sm" />
                            </div>
                            <div class="space-y-1.5">
                                <label for="shop-longitude" class="text-sm font-medium">Longitude</label>
                                <Input id="shop-longitude" v-model="lng" placeholder="e.g. 120.9842"
                                    class="font-mono text-sm" />
                            </div>
                        </div>

                        <p class="text-xs text-muted-foreground">
                            You can get coordinates from
                            <a href="https://www.google.com/maps" target="_blank" rel="noopener"
                                class="underline hover:text-foreground">Google Maps</a>
                            — right-click your shop location and copy the
                            coordinates. Or use the button below to auto-detect
                            your current location.
                        </p>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <p class="mr-auto text-xs text-muted-foreground sm:mr-0" aria-live="polite">
                                {{
                                    geoDirty
                                        ? 'You have unsaved changes'
                                        : 'No changes yet'
                                }}
                            </p>
                            <Button v-if="hasGeo" variant="outline" class="border-red-200 text-red-600 hover:bg-red-50"
                                :disabled="clearing" @click="clearGeo">
                                <Loader2 v-if="clearing" class="mr-1.5 h-4 w-4 animate-spin" />
                                <Trash2 v-else class="mr-1.5 h-4 w-4" />
                                {{ clearing ? 'Clearing…' : 'Clear Location' }}
                            </Button>
                            <Button variant="outline" :disabled="locating" @click="useMyLocation">
                                <Loader2 v-if="locating" class="mr-1.5 h-4 w-4 animate-spin" />
                                <Navigation v-else class="mr-1.5 h-4 w-4" />
                                {{
                                    locating
                                        ? 'Detecting…'
                                        : 'Use My Current Location'
                                }}
                            </Button>
                            <Button :disabled="saving || !geoDirty" @click="saveGeo">
                                <Loader2 v-if="saving" class="mr-1.5 h-4 w-4 animate-spin" />
                                {{ saving ? 'Saving…' : 'Save Location' }}
                            </Button>
                        </div>
                    </section>

                    <section id="data" class="scroll-mt-4 space-y-4 border-t pt-8">
                        <div>
                            <div class="flex items-center gap-2">
                                <Database class="h-4 w-4 text-muted-foreground" />
                                <h3 class="text-base font-semibold">
                                    Your Data
                                </h3>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Keep your own copy of this shop's records. Files
                                are CSV, which opens in Excel or Google Sheets.
                            </p>
                        </div>

                        <Button as-child class="gap-1.5">
                            <a href="/shop/export" download>
                                <Download class="h-4 w-4" aria-hidden="true" />
                                Download all my data (.zip)
                            </a>
                        </Button>
                    </section>
                </div>

                <!-- ── Side panel ── -->
                <aside class="lg:sticky lg:top-4 lg:self-start">
                    <Card>
                        <CardContent class="space-y-4 p-4">
                            <div class="flex items-center gap-2">
                                <Building2 class="h-4 w-4 text-muted-foreground" />
                                <h3 class="text-sm font-semibold">
                                    Shop Information
                                </h3>
                            </div>
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-xs text-muted-foreground">
                                        Shop Name
                                    </dt>
                                    <dd class="font-medium">
                                        {{ props.shop.shop_name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted-foreground">
                                        Phone
                                    </dt>
                                    <dd class="font-medium">
                                        {{ props.shop.phone || '—' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted-foreground">
                                        Address
                                    </dt>
                                    <dd class="font-medium">
                                        {{ addressLine }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted-foreground">
                                        Status
                                    </dt>
                                    <dd class="font-medium capitalize">
                                        {{ props.shop.status }}
                                    </dd>
                                </div>
                            </dl>

                            <div class="border-t pt-4">
                                <p class="mb-2 text-xs font-medium text-muted-foreground">
                                    Jump to
                                </p>
                                <nav aria-label="Settings sections" class="flex flex-col">
                                    <a v-for="link in sectionLinks" :key="link.href" :href="link.href"
                                        class="rounded-md px-2 py-1.5 text-sm text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                        {{ link.label }}
                                    </a>
                                </nav>
                            </div>

                            <div class="border-t pt-4">
                                <p class="mb-2 text-xs text-muted-foreground">
                                    Control customer pickup and delivery without
                                    deleting historical records.
                                </p>
                                <Button variant="default" class="w-full bg-blue-600 text-white hover:bg-blue-700"
                                    @click="router.visit('/shop/setup')">
                                    <SlidersHorizontal class="mr-1.5 h-4 w-4" />
                                    Review Operations Setup
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>
