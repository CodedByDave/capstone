<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    ClipboardList,
    MapPin,
    Package,
    Search,
    WashingMachine,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Order {
    id: number;
    payment_public_id: string;
    order_number: string;
    shop: { name: string; location: string } | null;
    service_name: string;
    status: 'pending' | 'in_progress' | 'completed' | 'picked_up' | 'cancelled';
    payment_status: 'unpaid' | 'partial' | 'paid';
    is_rush?: boolean;
    total_amount: number;
    order_source: string;
    pickup_type: string;
    created_at: string;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    total: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

interface Filters {
    status: string | null;
    payment: string | null;
    search: string | null;
}

interface Counts {
    all: number;
    pending: number;
    in_progress: number;
    completed: number;
    cancelled: number;
}

const props = defineProps<{
    orders: PaginatedOrders;
    counts: Counts;
    filters: Filters;
}>();

// ─── Filters ──────────────────────────────────────────────────────────────────

const tabs = computed(() => [
    { key: null, label: 'All', count: props.counts.all },
    { key: 'pending', label: 'Pending', count: props.counts.pending },
    {
        key: 'in_progress',
        label: 'In progress',
        count: props.counts.in_progress,
    },
    { key: 'completed', label: 'Completed', count: props.counts.completed },
    { key: 'cancelled', label: 'Cancelled', count: props.counts.cancelled },
]);

const paymentOptions = [
    { value: '', label: 'Any payment' },
    { value: 'unpaid', label: 'Unpaid' },
    { value: 'partial', label: 'Partially paid' },
    { value: 'paid', label: 'Paid' },
];

const searchText = ref(props.filters.search ?? '');
let searchTimer: ReturnType<typeof setTimeout> | undefined;

const hasFilters = computed(
    () =>
        props.filters.status !== null ||
        props.filters.payment !== null ||
        props.filters.search !== null,
);

/** Loads the list with the given filters; any change goes back to page 1. */
function applyFilters(next: Partial<Filters>, page = 1) {
    const merged = { ...props.filters, ...next };

    router.get(
        '/user/orders',
        {
            status: merged.status || undefined,
            payment: merged.payment || undefined,
            search: merged.search || undefined,
            page: page > 1 ? page : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['orders', 'counts', 'filters'],
        },
    );
}

function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(
        () => applyFilters({ search: searchText.value.trim() || null }),
        350,
    );
}

function clearSearch() {
    searchText.value = '';
    clearTimeout(searchTimer);
    applyFilters({ search: null });
}

function resetFilters() {
    searchText.value = '';
    clearTimeout(searchTimer);
    applyFilters({ status: null, payment: null, search: null });
}

onBeforeUnmount(() => clearTimeout(searchTimer));

// ─── Toast ────────────────────────────────────────────────────────────────────

const page = usePage();

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flashToast?.message) {
        switch (flashToast.type) {
            case 'success':
                toast.success(flashToast.message);
                break;
            case 'error':
                toast.error(flashToast.message);
                break;
            default:
                toast.info(flashToast.message);
        }
    }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; classes: string }> = {
    pending: { label: 'Pending', classes: 'bg-yellow-100 text-yellow-700' },
    in_progress: { label: 'In Progress', classes: 'bg-blue-100 text-blue-700' },
    completed: { label: 'Completed', classes: 'bg-green-100 text-green-700' },
    picked_up: { label: 'Picked Up', classes: 'bg-slate-100 text-slate-700' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-600' },
};

const paymentConfig: Record<string, { label: string; classes: string }> = {
    unpaid: { label: 'Unpaid', classes: 'bg-red-50 text-red-600' },
    partial: { label: 'Partial', classes: 'bg-orange-50 text-orange-600' },
    paid: { label: 'Paid', classes: 'bg-emerald-50 text-emerald-700' },
};

function currency(val: number) {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}
</script>

<template>
    <Head title="My Orders" />
    <UserLayout>
        <div class="px-5 pt-6">
            <!-- Header -->
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h1
                        class="font-display text-[28px] leading-9 font-bold text-slate-900"
                    >
                        My Orders
                    </h1>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ counts.all }} total
                    </p>
                </div>
                <ClipboardList
                    class="mb-1 h-5 w-5 text-blue-600"
                    aria-hidden="true"
                />
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-4 px-5">
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    aria-hidden="true"
                />
                <input
                    v-model="searchText"
                    type="search"
                    autocomplete="off"
                    placeholder="Search order number or shop"
                    aria-label="Search your orders"
                    class="block min-h-12 w-full rounded-xl border border-slate-200 bg-white pr-10 pl-9 text-[15px] text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/25 focus:outline-none"
                    @input="onSearchInput"
                />
                <button
                    v-if="searchText"
                    type="button"
                    class="absolute top-1/2 right-1 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                    aria-label="Clear search"
                    @click="clearSearch"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div
            class="mt-3 overflow-x-auto border-b border-slate-100 px-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            role="tablist"
            aria-label="Filter orders by status"
        >
            <div class="flex min-w-max gap-5">
                <button
                    v-for="tab in tabs"
                    :key="tab.label"
                    type="button"
                    role="tab"
                    :aria-selected="filters.status === tab.key"
                    class="relative min-h-11 pb-1 text-sm font-medium whitespace-nowrap transition-colors"
                    :class="
                        filters.status === tab.key
                            ? 'text-slate-900'
                            : 'text-slate-500 hover:text-slate-700'
                    "
                    @click="applyFilters({ status: tab.key })"
                >
                    {{ tab.label }}
                    <span
                        class="ml-1 text-xs"
                        :class="
                            filters.status === tab.key
                                ? 'text-blue-600'
                                : 'text-slate-400'
                        "
                        >{{ tab.count }}</span
                    >
                    <span
                        v-if="filters.status === tab.key"
                        class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-blue-600"
                    />
                </button>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-3 px-5">
            <label for="orders-payment" class="sr-only">
                Filter by payment
            </label>
            <select
                id="orders-payment"
                class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/25 focus:outline-none"
                :value="filters.payment ?? ''"
                @change="
                    applyFilters({
                        payment:
                            ($event.target as HTMLSelectElement).value || null,
                    })
                "
            >
                <option
                    v-for="option in paymentOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <button
                v-if="hasFilters"
                type="button"
                class="min-h-11 text-sm font-semibold text-blue-600 hover:underline focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                @click="resetFilters"
            >
                Clear filters
            </button>
            <span class="ml-auto text-xs text-slate-500" aria-live="polite">
                {{ orders.total }}
                {{ orders.total === 1 ? 'order' : 'orders' }}
            </span>
        </div>

        <!-- Empty state -->
        <div
            v-if="orders.data.length === 0 && hasFilters"
            class="flex flex-col items-center px-5 py-16 text-center"
        >
            <Search class="mb-3 h-10 w-10 text-slate-300" aria-hidden="true" />
            <p class="font-display text-sm font-semibold text-slate-900">
                No orders match these filters
            </p>
            <p class="mt-1 max-w-60 text-xs leading-5 text-slate-500">
                Try another status or search, or clear the filters to see all
                your orders.
            </p>
            <button
                type="button"
                class="mt-5 flex min-h-12 items-center justify-center rounded-xl border border-blue-600 px-6 text-sm font-semibold text-blue-600 transition hover:bg-blue-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                @click="resetFilters"
            >
                Clear filters
            </button>
        </div>

        <div
            v-else-if="orders.data.length === 0"
            class="flex flex-col items-center px-5 py-20 text-center"
        >
            <Package class="mb-3 h-10 w-10 text-slate-300" aria-hidden="true" />
            <p class="font-display text-sm font-semibold text-slate-900">
                No orders yet
            </p>
            <p class="mt-1 max-w-60 text-xs leading-5 text-slate-500">
                Browse shops and place your first order.
            </p>
            <Link
                href="/user/shops"
                class="mt-5 flex min-h-12 items-center justify-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-none"
            >
                Browse shops
            </Link>
        </div>

        <!-- Order list -->
        <div v-else>
            <ul
                class="mt-4 divide-y divide-slate-100 border-t border-slate-100"
            >
                <li v-for="order in orders.data" :key="order.id">
                    <Link
                        :href="`/user/orders/${order.payment_public_id}`"
                        class="flex w-full items-start gap-3 px-5 py-4 text-left transition hover:bg-slate-50 focus-visible:bg-slate-50 focus-visible:outline-none"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                            aria-hidden="true"
                        >
                            <WashingMachine class="h-5 w-5" />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="flex items-start justify-between gap-3"
                            >
                                <span
                                    class="truncate font-display text-sm font-semibold text-slate-900"
                                >
                                    {{ order.shop?.name ?? '—' }}
                                </span>
                                <span
                                    class="shrink-0 text-sm font-semibold text-slate-900 tabular-nums"
                                >
                                    {{ currency(order.total_amount) }}
                                </span>
                            </span>

                            <span class="block text-xs text-slate-500">
                                {{ order.order_number }}
                            </span>
                            <span
                                class="mt-1 flex items-center gap-1.5 text-xs text-slate-500"
                            >
                                <MapPin
                                    class="h-3 w-3 shrink-0 text-slate-400"
                                    aria-hidden="true"
                                />
                                <span class="truncate">
                                    {{ order.shop?.location ?? '—' }}
                                </span>
                            </span>
                            <span class="block text-xs text-slate-500">
                                {{ order.service_name }}
                            </span>

                            <span
                                class="mt-2 flex flex-wrap items-center gap-1.5"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                    :class="
                                        statusConfig[order.status]?.classes ??
                                        'bg-slate-100 text-slate-600'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                        aria-hidden="true"
                                    />
                                    {{
                                        statusConfig[order.status]?.label ??
                                        order.status
                                    }}
                                </span>
                                <span
                                    v-if="order.is_rush"
                                    class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-[11px] font-semibold text-orange-700"
                                >
                                    <Zap class="h-3 w-3" aria-hidden="true" />
                                    Rush
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                    :class="
                                        paymentConfig[order.payment_status]
                                            ?.classes ??
                                        'bg-slate-100 text-slate-600'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                        aria-hidden="true"
                                    />
                                    {{
                                        paymentConfig[order.payment_status]
                                            ?.label ?? order.payment_status
                                    }}
                                </span>
                            </span>
                        </span>

                        <ChevronRight
                            class="mt-2 h-4 w-4 shrink-0 text-slate-300"
                            aria-hidden="true"
                        />
                    </Link>
                </li>
            </ul>

            <!-- Pagination -->
            <nav
                v-if="orders.last_page > 1"
                class="flex items-center justify-center gap-3 px-5 py-6"
                aria-label="Orders pages"
            >
                <button
                    type="button"
                    class="min-h-11 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none disabled:opacity-40"
                    :disabled="orders.current_page <= 1"
                    @click="applyFilters({}, orders.current_page - 1)"
                >
                    Previous
                </button>
                <span class="text-xs text-slate-500">
                    {{ orders.current_page }} / {{ orders.last_page }}
                </span>
                <button
                    type="button"
                    class="min-h-11 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none disabled:opacity-40"
                    :disabled="orders.current_page >= orders.last_page"
                    @click="applyFilters({}, orders.current_page + 1)"
                >
                    Next
                </button>
            </nav>
        </div>
    </UserLayout>
</template>
