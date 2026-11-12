<script setup lang="ts">
import DeliveryPricingCard, {
    type DeliveryPricing,
} from '@/components/business/DeliveryPricingCard.vue';
import OperatingHoursCard from '@/components/business/OperatingHoursCard.vue';
import RiderLoadLimitCard from '@/components/business/RiderLoadLimitCard.vue';
import RushFeeCard from '@/components/business/RushFeeCard.vue';
import SettingsSaveBar from '@/components/business/SettingsSaveBar.vue';
import SmsGatewayCard, {
    type SmsGatewaySettings,
} from '@/components/business/SmsGatewayCard.vue';
import LocationPin from '@/components/map/LocationPin.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { provideSettingsSaveBar } from '@/composables/useSettingsSaveBar';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    ArrowRight,
    Building2,
    CheckCircle2,
    Clock,
    Database,
    Download,
    ImagePlus,
    Loader2,
    MapPin,
    MessageSquareText,
    Navigation,
    SlidersHorizontal,
    Trash2,
    Truck,
    Upload,
} from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, type Component } from 'vue';
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
    rushFee: number;
    riderLoadLimit: { min: number | null; max: number | null };
    smsGateway: SmsGatewaySettings;
    riderVehicle: {
        selected: string;
        choices: string[];
        limits: { type: string; min: number | null; max: number | null }[];
    };
    operatingHours: {
        is_open_24_7: boolean;
        opens_at: string;
        closes_at: string;
        operating_days: number[];
    };
}>();

// One "Save all changes" bar for the hours, pricing, rider load, rush fee and location sections.
const settingsBar = provideSettingsSaveBar();

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
        preserveState: true,
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
            preserveState: true,
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
// ─── Tabs ──────────────────────────────────────────────────────────────────────
// One tab per group instead of one long page. Every section stays mounted, so unsaved changes on
// another tab are kept and the single save bar still saves them all.

type TabId = 'hours' | 'delivery' | 'notifications' | 'profile' | 'data';

const tabs = computed<
    { id: TabId; label: string; icon: Component; sections: string[] }[]
>(() => [
    { id: 'hours', label: 'Operating Hours', icon: Clock, sections: ['hours'] },
    {
        id: 'delivery',
        label: 'Delivery & Fees',
        icon: Truck,
        sections: ['delivery', 'rider-load', 'rush'],
    },
    {
        id: 'notifications',
        label: 'Notifications',
        icon: MessageSquareText,
        sections: ['sms'],
    },
    {
        id: 'profile',
        label: 'Profile & Location',
        icon: MapPin,
        sections: ['cover', 'location'],
    },
    { id: 'data', label: 'Your Data', icon: Database, sections: ['data'] },
]);

const activeTab = ref<TabId>('hours');

// Tabs holding unsaved changes get a dot, so nothing is forgotten on a tab out of view.
const dirtyTabs = computed(
    () =>
        new Set(
            settingsBar.dirty.value.map(
                (section) =>
                    tabs.value.find((tab) => tab.sections.includes(section.key))
                        ?.id,
            ),
        ),
);

function selectTab(id: TabId, focus = false) {
    activeTab.value = id;
    // The address keeps the tab, so a reload or a shared link opens the same one.
    window.history.replaceState(window.history.state, '', `#${id}`);
    if (focus) nextTick(() => document.getElementById(`tab-${id}`)?.focus());
}

// Previous and Next walk through the tabs in order, like the steps of a setup.
const activeIndex = computed(() =>
    tabs.value.findIndex((tab) => tab.id === activeTab.value),
);
const previousTab = computed(() => tabs.value[activeIndex.value - 1] ?? null);
const nextTab = computed(() => tabs.value[activeIndex.value + 1] ?? null);

function goToTab(id: TabId) {
    selectTab(id);
    nextTick(() =>
        document
            .getElementById('settings-tabs')
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' }),
    );
}

// Arrow keys, Home and End move between tabs, as screen reader users expect from a tab list.
function onTabKeydown(event: KeyboardEvent, index: number) {
    const last = tabs.value.length - 1;
    const moves: Record<string, number> = {
        ArrowRight: index === last ? 0 : index + 1,
        ArrowLeft: index === 0 ? last : index - 1,
        Home: 0,
        End: last,
    };
    const next = moves[event.key];
    if (next === undefined) return;
    event.preventDefault();
    selectTab(tabs.value[next].id, true);
}

// Old links such as /shop/settings#rider-load still work: they open the tab holding that section.
onMounted(() => {
    const target = window.location.hash.replace('#', '');
    const tab = tabs.value.find(
        (item) => item.id === target || item.sections.includes(target),
    );
    if (!tab) return;
    activeTab.value = tab.id;
    if (target !== tab.id) {
        nextTick(() =>
            document.getElementById(target)?.scrollIntoView({ block: 'start' }),
        );
    }
});

const pinLat = computed(() => {
    const value = parseFloat(lat.value);
    return Number.isFinite(value) ? value : null;
});
const pinLng = computed(() => {
    const value = parseFloat(lng.value);
    return Number.isFinite(value) ? value : null;
});

function pickOnMap(point: { lat: number; lng: number }) {
    lat.value = point.lat.toFixed(6);
    lng.value = point.lng.toFixed(6);
}

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
            toast.success('Location detected. Save your changes to confirm.');
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

function saveGeo(): Promise<boolean> {
    const parsedLat = lat.value ? parseFloat(lat.value) : null;
    const parsedLng = lng.value ? parseFloat(lng.value) : null;

    if ((lat.value && isNaN(parsedLat!)) || (lng.value && isNaN(parsedLng!))) {
        toast.error('Please enter valid coordinates.');
        return Promise.resolve(false);
    }

    saving.value = true;

    return new Promise((resolve) => {
        router.post(
            '/shop/settings/geo',
            {
                latitude: parsedLat,
                longitude: parsedLng,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => resolve(true),
                onError: () => resolve(false),
                onCancel: () => resolve(false),
                onFinish: () => {
                    saving.value = false;
                },
            },
        );
    });
}

function discardGeo() {
    lat.value = props.shop.latitude?.toString() ?? '';
    lng.value = props.shop.longitude?.toString() ?? '';
}

settingsBar.sections.set('location', {
    key: 'location',
    label: 'Shop location',
    isDirty: () => geoDirty.value,
    save: saveGeo,
    discard: discardGeo,
});

function clearGeo() {
    clearing.value = true;
    router.post(
        '/shop/settings/geo/clear',
        {},
        {
            preserveScroll: true,
            preserveState: true,
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

            <!-- ── Tabs ── -->
            <div
                id="settings-tabs"
                role="tablist"
                aria-label="Settings"
                class="-mx-6 flex gap-1 overflow-x-auto border-b px-6"
            >
                <button
                    v-for="(tab, index) in tabs"
                    :id="`tab-${tab.id}`"
                    :key="tab.id"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.id"
                    :aria-controls="`panel-${tab.id}`"
                    :tabindex="activeTab === tab.id ? 0 : -1"
                    class="relative -mb-px flex min-h-11 shrink-0 items-center gap-2 border-b-2 px-3 text-sm font-medium whitespace-nowrap transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        activeTab === tab.id
                            ? 'border-primary text-foreground'
                            : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'
                    "
                    @click="selectTab(tab.id)"
                    @keydown="onTabKeydown($event, index)"
                >
                    <component
                        :is="tab.icon"
                        class="h-4 w-4"
                        :class="activeTab === tab.id ? 'text-primary' : ''"
                        aria-hidden="true"
                    />
                    {{ tab.label }}
                    <span
                        v-if="dirtyTabs.has(tab.id)"
                        class="h-2 w-2 rounded-full bg-amber-500"
                        role="img"
                        aria-label="unsaved changes"
                    />
                </button>
            </div>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
                <!-- ── Sections ── -->
                <div class="min-w-0">
                    <!-- Operating Hours -->
                    <div
                        v-show="activeTab === 'hours'"
                        id="panel-hours"
                        role="tabpanel"
                        aria-labelledby="tab-hours"
                    >
                        <div id="hours" class="scroll-mt-4">
                            <OperatingHoursCard :hours="props.operatingHours" />
                        </div>
                    </div>

                    <!-- Delivery & Fees -->
                    <div
                        v-show="activeTab === 'delivery'"
                        id="panel-delivery"
                        role="tabpanel"
                        aria-labelledby="tab-delivery"
                        class="space-y-8"
                    >
                        <div id="delivery" class="scroll-mt-4">
                            <DeliveryPricingCard
                                :pricing="props.deliveryPricing"
                            />
                        </div>

                        <!-- Rider Load Limit -->
                        <div
                            v-if="props.deliveryPricing.offers_delivery"
                            id="rider-load"
                            class="scroll-mt-4 border-t pt-8"
                        >
                            <RiderLoadLimitCard
                                :limit="props.riderLoadLimit"
                                :vehicle="props.riderVehicle"
                            />
                        </div>

                        <!-- Rush Fee -->
                        <div id="rush" class="scroll-mt-4 border-t pt-8">
                            <RushFeeCard :rush-fee="props.rushFee" />
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div
                        v-show="activeTab === 'notifications'"
                        id="panel-notifications"
                        role="tabpanel"
                        aria-labelledby="tab-notifications"
                    >
                        <div id="sms" class="scroll-mt-4">
                            <SmsGatewayCard :settings="props.smsGateway" />
                        </div>
                    </div>

                    <!-- Profile & Location -->
                    <div
                        v-show="activeTab === 'profile'"
                        id="panel-profile"
                        role="tabpanel"
                        aria-labelledby="tab-profile"
                        class="space-y-8"
                    >
                        <!-- Cover Photo -->
                        <section id="cover" class="scroll-mt-4 space-y-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <ImagePlus
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <h3 class="text-base font-semibold">
                                        Cover Photo
                                    </h3>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    This photo appears on your shop card in the
                                    user dashboard and shop listing.
                                </p>
                            </div>

                            <div
                                class="group relative flex h-48 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-muted/30"
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
                                    <p class="text-xs">
                                        No cover photo uploaded
                                    </p>
                                </div>

                                <button
                                    v-if="coverPreview"
                                    type="button"
                                    aria-label="Remove cover photo"
                                    class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow transition group-hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
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

                            <input
                                ref="coverInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onCoverChange"
                            />
                            <p class="text-xs text-muted-foreground">
                                PNG, JPG or WEBP — max 4 MB.
                            </p>

                            <div
                                class="flex flex-wrap items-center justify-end gap-3"
                            >
                                <p
                                    class="text-xs text-muted-foreground"
                                    aria-live="polite"
                                >
                                    {{
                                        coverFile
                                            ? 'Photo ready to upload'
                                            : 'Photos are uploaded right away'
                                    }}
                                </p>
                                <Button
                                    variant="primary-outline"
                                    @click="coverInput?.click()"
                                >
                                    <Upload class="mr-1.5 h-4 w-4" />
                                    {{
                                        coverPreview
                                            ? 'Choose another photo'
                                            : 'Choose photo'
                                    }}
                                </Button>
                                <Button
                                    :disabled="!coverFile || uploadingCover"
                                    @click="uploadCover"
                                >
                                    <Loader2
                                        v-if="uploadingCover"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    {{
                                        uploadingCover
                                            ? 'Uploading…'
                                            : 'Upload photo'
                                    }}
                                </Button>
                            </div>
                        </section>

                        <!-- Geolocation -->
                        <section
                            id="location"
                            class="scroll-mt-4 space-y-5 border-t pt-8"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <MapPin
                                        class="h-4 w-4 text-muted-foreground"
                                    />
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

                            <div
                                v-if="hasGeo"
                                class="flex items-center justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3"
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
                                        <p
                                            class="font-mono text-xs text-emerald-700"
                                        >
                                            {{
                                                props.shop.latitude?.toFixed(6)
                                            }},
                                            {{
                                                props.shop.longitude?.toFixed(6)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3"
                            >
                                <MapPin
                                    class="h-4 w-4 shrink-0 text-amber-600"
                                />
                                <p class="text-xs text-amber-700">
                                    No location set. Staff clock-in will not
                                    check distance.
                                </p>
                            </div>

                            <div
                                v-if="props.shop.status !== 'active'"
                                class="flex items-start gap-2 rounded-xl bg-amber-50 px-4 py-3"
                            >
                                <AlertTriangle
                                    class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                                />
                                <p class="text-xs text-amber-700">
                                    Your shop is currently
                                    <span class="font-semibold capitalize">{{
                                        props.shop.status
                                    }}</span>
                                    and will not appear in user searches until
                                    it is approved and active.
                                </p>
                            </div>

                            <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label
                                        for="shop-latitude"
                                        class="text-sm font-medium"
                                        >Latitude</label
                                    >
                                    <Input
                                        id="shop-latitude"
                                        v-model="lat"
                                        placeholder="e.g. 14.5995"
                                        class="font-mono text-sm"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        for="shop-longitude"
                                        class="text-sm font-medium"
                                        >Longitude</label
                                    >
                                    <Input
                                        id="shop-longitude"
                                        v-model="lng"
                                        placeholder="e.g. 120.9842"
                                        class="font-mono text-sm"
                                    />
                                </div>
                            </div>

                            <LocationPin
                                v-if="pinLat !== null && pinLng !== null"
                                :lat="pinLat"
                                :lng="pinLng"
                                label="your shop"
                                pickable
                                height-class="h-64"
                                @pick="pickOnMap"
                            />
                            <p class="text-xs text-muted-foreground">
                                Click the map or drag the pin to your shop's
                                exact spot, type the coordinates, or use the
                                button below to auto-detect your current
                                location.
                            </p>

                            <div
                                class="flex flex-wrap items-center justify-end gap-3"
                            >
                                <Button
                                    v-if="hasGeo"
                                    variant="outline"
                                    class="border-red-200 text-red-600 hover:bg-red-50"
                                    :disabled="clearing"
                                    @click="clearGeo"
                                >
                                    <Loader2
                                        v-if="clearing"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    <Trash2 v-else class="mr-1.5 h-4 w-4" />
                                    {{
                                        clearing
                                            ? 'Clearing…'
                                            : 'Clear Location'
                                    }}
                                </Button>
                                <Button
                                    variant="outline"
                                    :disabled="locating"
                                    @click="useMyLocation"
                                >
                                    <Loader2
                                        v-if="locating"
                                        class="mr-1.5 h-4 w-4 animate-spin"
                                    />
                                    <Navigation v-else class="mr-1.5 h-4 w-4" />
                                    {{
                                        locating
                                            ? 'Detecting…'
                                            : 'Use My Current Location'
                                    }}
                                </Button>
                            </div>
                        </section>
                    </div>

                    <!-- Your Data -->
                    <div
                        v-show="activeTab === 'data'"
                        id="panel-data"
                        role="tabpanel"
                        aria-labelledby="tab-data"
                    >
                        <section id="data" class="scroll-mt-4 space-y-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <Database
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <h3 class="text-base font-semibold">
                                        Your Data
                                    </h3>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Keep your own copy of this shop's records.
                                    Files are CSV, which opens in Excel or
                                    Google Sheets.
                                </p>
                            </div>

                            <Button as-child class="gap-1.5">
                                <a href="/shop/export" download>
                                    <Download
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                    Download all my data (.zip)
                                </a>
                            </Button>
                        </section>
                    </div>

                    <!-- Previous / Next -->
                    <nav
                        aria-label="Settings pages"
                        class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t pt-6"
                    >
                        <Button
                            v-if="previousTab"
                            type="button"
                            variant="back"
                            class="min-h-11"
                            @click="goToTab(previousTab.id)"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            {{ previousTab.label }}
                        </Button>
                        <span v-else />
                        <span class="text-xs text-muted-foreground">
                            {{ activeIndex + 1 }} of {{ tabs.length }}
                        </span>
                        <Button
                            v-if="nextTab"
                            type="button"
                            class="min-h-11"
                            @click="goToTab(nextTab.id)"
                        >
                            Next: {{ nextTab.label }}
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                        <span v-else />
                    </nav>

                    <SettingsSaveBar
                        :dirty="settingsBar.dirty.value"
                        :saving="settingsBar.saving.value"
                        @save="settingsBar.saveAll"
                        @discard="settingsBar.discardAll"
                    />
                </div>

                <!-- ── Side panel ── -->
                <aside class="lg:sticky lg:top-4 lg:self-start">
                    <Card>
                        <CardContent class="space-y-4 p-4">
                            <div class="flex items-center gap-2">
                                <Building2
                                    class="h-4 w-4 text-muted-foreground"
                                />
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
                                <p class="mb-2 text-xs text-muted-foreground">
                                    Control customer pickup and delivery without
                                    deleting historical records.
                                </p>
                                <Button
                                    variant="default"
                                    class="w-full bg-blue-600 text-white hover:bg-blue-700"
                                    @click="router.visit('/shop/setup')"
                                >
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
