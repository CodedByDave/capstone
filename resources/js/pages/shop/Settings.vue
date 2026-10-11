<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Building2,
    CheckCircle2,
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
        <div class="mx-auto max-w-2xl space-y-6 px-6">
            <!-- ── Header ── -->
            <div>
                <h2 class="text-lg font-semibold">Shop Settings</h2>
                <p class="text-sm text-muted-foreground">
                    Manage your shop profile and location settings.
                </p>
            </div>

            <!-- ── Shop Info (read-only) ── -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                        <CardTitle class="text-sm">Shop Information</CardTitle>
                    </div>
                    <CardDescription class="text-xs"
                        >Your registered shop details.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="mb-0.5 text-xs text-muted-foreground">
                                Shop Name
                            </p>
                            <p class="font-medium">
                                {{ props.shop.shop_name }}
                            </p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-xs text-muted-foreground">
                                Phone
                            </p>
                            <p class="font-medium">
                                {{ props.shop.phone || '—' }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="mb-0.5 text-xs text-muted-foreground">
                                Address
                            </p>
                            <p class="font-medium">
                                {{
                                    [
                                        props.shop.block_street,
                                        props.shop.barangay,
                                        props.shop.municipality,
                                        props.shop.postal_code,
                                    ]
                                        .filter(Boolean)
                                        .join(', ') || '—'
                                }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <SlidersHorizontal
                            class="h-4 w-4 text-muted-foreground"
                        />
                        <CardTitle class="text-sm">Operations Setup</CardTitle>
                    </div>
                    <CardDescription class="text-xs">
                        Control branch management, customer pickup, and delivery
                        without deleting historical records.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit('/shop/setup')"
                    >
                        Review Operations Setup
                    </Button>
                </CardContent>
            </Card>

            <!-- ── Cover Photo ── -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <ImagePlus class="h-4 w-4 text-muted-foreground" />
                        <CardTitle class="text-sm">Cover Photo</CardTitle>
                    </div>
                    <CardDescription class="text-xs">
                        This photo appears on your shop card in the user
                        dashboard and shop listing.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Preview area -->
                    <div
                        class="group relative flex h-48 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50"
                    >
                        <img
                            v-if="coverPreview"
                            :src="coverPreview"
                            alt="Cover photo"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center gap-2 text-muted-foreground"
                        >
                            <ImagePlus class="h-10 w-10 opacity-30" />
                            <p class="text-xs">No cover photo uploaded</p>
                        </div>

                        <!-- Remove button overlay -->
                        <button
                            v-if="coverPreview"
                            type="button"
                            class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow transition group-hover:opacity-100"
                            :disabled="removingCover"
                            @click="removeCover"
                        >
                            <Loader2
                                v-if="removingCover"
                                class="h-4 w-4 animate-spin"
                            />
                            <Trash2 v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- File input + actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            ref="coverInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onCoverChange"
                        />

                        <Button
                            variant="outline"
                            size="sm"
                            @click="coverInput?.click()"
                        >
                            <Upload class="mr-1.5 h-3.5 w-3.5" />
                            {{
                                coverPreview ? 'Replace Photo' : 'Upload Photo'
                            }}
                        </Button>

                        <Button
                            v-if="coverFile"
                            size="sm"
                            :disabled="uploadingCover"
                            @click="uploadCover"
                        >
                            <Loader2
                                v-if="uploadingCover"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ uploadingCover ? 'Saving…' : 'Save Photo' }}
                        </Button>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        PNG, JPG or WEBP — max 4 MB.
                    </p>
                </CardContent>
            </Card>

            <!-- ── Geolocation ── -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                        <CardTitle class="text-sm"
                            >Shop Location (GPS)</CardTitle
                        >
                    </div>
                    <CardDescription class="text-xs">
                        Set your shop's GPS coordinates to enable
                        geolocation-based attendance. Staff must be within 500 m
                        to clock in; otherwise they are auto-marked absent.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Current location status -->
                    <div
                        v-if="hasGeo"
                        class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle2
                                class="h-4 w-4 shrink-0 text-emerald-600"
                            />
                            <div>
                                <p
                                    class="text-xs font-semibold text-emerald-700"
                                >
                                    Location is set
                                </p>
                                <p class="font-mono text-xs text-emerald-600">
                                    {{ props.shop.latitude?.toFixed(6) }},
                                    {{ props.shop.longitude?.toFixed(6) }}
                                </p>
                            </div>
                        </div>
                        <a
                            v-if="mapsUrl"
                            :href="mapsUrl"
                            target="_blank"
                            class="flex items-center gap-1 text-xs font-medium text-emerald-700 hover:underline"
                        >
                            View on Maps
                            <ExternalLink class="h-3 w-3" />
                        </a>
                    </div>
                    <div
                        v-else
                        class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
                    >
                        <MapPin class="h-4 w-4 shrink-0 text-amber-500" />
                        <p class="text-xs text-amber-700">
                            No location set. Staff clock-in will not check
                            distance.
                        </p>
                    </div>

                    <!-- Visibility warning -->
                    <div
                        v-if="props.shop.status !== 'active'"
                        class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
                    >
                        <AlertTriangle
                            class="mt-0.5 h-4 w-4 shrink-0 text-amber-500"
                        />
                        <p class="text-xs text-amber-700">
                            Your shop is currently
                            <span class="font-semibold capitalize">{{
                                props.shop.status
                            }}</span>
                            and will not appear in user searches until it is
                            approved and active.
                        </p>
                    </div>

                    <!-- Coordinate inputs -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Latitude</label>
                            <Input
                                v-model="lat"
                                placeholder="e.g. 14.5995"
                                class="h-9 font-mono text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Longitude</label>
                            <Input
                                v-model="lng"
                                placeholder="e.g. 120.9842"
                                class="h-9 font-mono text-sm"
                            />
                        </div>
                    </div>

                    <!-- Help text -->
                    <p class="text-xs text-muted-foreground">
                        You can get coordinates from
                        <a
                            href="https://www.google.com/maps"
                            target="_blank"
                            class="underline hover:text-foreground"
                            >Google Maps</a
                        >
                        — right-click your shop location and copy the
                        coordinates. Or use the button below to auto-detect your
                        current location.
                    </p>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="locating"
                            @click="useMyLocation"
                        >
                            <Loader2
                                v-if="locating"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            <Navigation v-else class="mr-1.5 h-3.5 w-3.5" />
                            {{
                                locating
                                    ? 'Detecting…'
                                    : 'Use My Current Location'
                            }}
                        </Button>

                        <Button size="sm" :disabled="saving" @click="saveGeo">
                            <Loader2
                                v-if="saving"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ saving ? 'Saving…' : 'Save Location' }}
                        </Button>

                        <Button
                            v-if="hasGeo"
                            variant="outline"
                            size="sm"
                            class="border-red-200 text-red-600 hover:bg-red-50"
                            :disabled="clearing"
                            @click="clearGeo"
                        >
                            <Loader2
                                v-if="clearing"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            <Trash2 v-else class="mr-1.5 h-3.5 w-3.5" />
                            {{ clearing ? 'Clearing…' : 'Clear Location' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
