<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import CustomerSearch, {
    type CustomerSuggestion,
} from '@/components/shop/CustomerSearch.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Loader2,
    Minus,
    PackageOpen,
    Plus,
    Receipt,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface Product {
    id: number;
    name: string;
    pack_size: number;
    unit: string;
    price: number;
    photo_url: string | null;
    in_stock: boolean;
    /** How many packs can still be sold (stock, capped at the per-order limit). */
    max_count: number;
}

const props = defineProps<{
    products: Product[];
    /** The shop's tax from its BIR registration: 12% VAT, 3% percentage tax, or none. */
    tax: { rate: number; label: string | null };
    paymentMethods: string[];
}>();

const page = usePage<AppPageProps>();
const retailBase = computed(() =>
    page.props.auth.user.role === 'owner'
        ? '/shop/operations/retail'
        : '/staff/operations/retail',
);

// Suggests customers the shop already has, so a returning customer is picked, not typed again.
const customerSearchUrl = computed(() =>
    page.props.auth.user.role === 'owner'
        ? '/shop/operations/customers/search'
        : '/staff/operations/customers/search',
);

// The existing customer staff picked from the search; the sale is linked to that exact record.
const pickedCustomer = ref<CustomerSuggestion | null>(null);

function onCustomerPicked(customer: CustomerSuggestion) {
    pickedCustomer.value = customer;
    form.shop_customer_id = customer.public_id;
    form.customer_name = customer.name;
    form.customer_phone = customer.phone ?? '';
    form.customer_email = customer.email ?? '';
}

// Staying on the details they typed, but as a new customer instead of the picked one.
function clearPickedCustomer() {
    pickedCustomer.value = null;
    form.shop_customer_id = '';
}

// ─── Cart ──────────────────────────────────────────

// Packs picked per product id.
const counts = reactive<Record<number, number>>({});

const countOf = (product: Product) => counts[product.id] ?? 0;

function setCount(product: Product, next: number) {
    const value = Math.max(0, Math.min(product.max_count, next));

    if (value === 0) delete counts[product.id];
    else counts[product.id] = value;
}

const picked = computed(() =>
    props.products
        .filter((product) => countOf(product) > 0)
        .map((product) => ({
            product,
            count: countOf(product),
            amount: Math.round(product.price * countOf(product) * 100) / 100,
        })),
);

// The server prices the sale again from the shop's own prices; this is what the customer is told.
const subtotal = computed(() =>
    picked.value.reduce((sum, line) => sum + line.amount, 0),
);
const taxAmount = computed(
    () =>
        Math.round((Math.round(subtotal.value * 100) * props.tax.rate) / 100) /
        100,
);
const total = computed(() => subtotal.value + taxAmount.value);

// ─── Form ──────────────────────────────────────────

const form = useForm({
    // Filled from the picked products when the sale is sent.
    retail_items: [] as { id: number; count: number }[],
    // The existing customer picked from the search, or empty for a new one.
    shop_customer_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    payment_method: 'cash',
    amount_tendered: '' as string | number,
});

const isCash = computed(() => form.payment_method === 'cash');
const tendered = computed(() =>
    form.amount_tendered === '' ? null : Number(form.amount_tendered),
);
const change = computed(() =>
    tendered.value === null ? 0 : Math.max(0, tendered.value - total.value),
);
const cashShort = computed(
    () => isCash.value && tendered.value !== null && tendered.value < total.value,
);

function submit() {
    form.transform((data) => ({
        ...data,
        retail_items: picked.value.map((line) => ({
            id: line.product.id,
            count: line.count,
        })),
        amount_tendered: isCash.value ? data.amount_tendered : null,
    })).post(retailBase.value, { preserveScroll: true });
}

const canSubmit = computed(
    () => picked.value.length > 0 && !cashShort.value && !form.processing,
);

// ─── Helpers ───────────────────────────────────────

const formatCurrency = (value: number) =>
    `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const inputClass =
    'w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500';
</script>

<template>
    <Head title="Record Sale" />

    <ShopLayout title="Record Sale">
        <div class="space-y-6 px-6">
            <PageHeader
                title="Record Retail Sale"
                description="A customer buying products only. This is saved apart from laundry orders."
            >
                <Button
                    variant="back"
                    class="h-11 gap-1.5"
                    @click="router.visit(retailBase)"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                    Back to Retail Products
                </Button>
            </PageHeader>

            <form
                class="grid gap-6 lg:grid-cols-[1fr_380px]"
                @submit.prevent="submit"
            >
                <!-- ── Products ──────────────────────────── -->
                <section aria-labelledby="products-title" class="space-y-3">
                    <h2 id="products-title" class="text-sm font-semibold">
                        Products
                    </h2>

                    <div
                        v-if="products.length === 0"
                        class="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center text-muted-foreground"
                    >
                        <PackageOpen class="h-8 w-8" aria-hidden="true" />
                        <p class="text-sm">
                            No retail products yet. Turn on “Sell as a retail
                            product” for an item in Inventory and set its price.
                        </p>
                    </div>

                    <ul v-else class="divide-y rounded-xl border bg-card">
                        <li
                            v-for="product in products"
                            :key="product.id"
                            class="flex flex-wrap items-center gap-3 p-4"
                            :class="{ 'opacity-60': !product.in_stock }"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="h-12 w-12 rounded-lg border object-cover"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded-lg border bg-muted"
                                aria-hidden="true"
                            >
                                <ShoppingBag
                                    class="h-5 w-5 text-muted-foreground"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatCurrency(product.price) }} per
                                    {{ product.pack_size }}
                                    {{ product.unit }} pack ·
                                    <span v-if="product.in_stock"
                                        >{{ product.max_count }} in stock</span
                                    >
                                    <span v-else class="text-red-600"
                                        >Out of stock</span
                                    >
                                </p>
                            </div>

                            <div
                                class="flex items-center gap-1"
                                role="group"
                                :aria-label="`Packs of ${product.name}`"
                            >
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="h-11 w-11"
                                    :disabled="countOf(product) === 0"
                                    :aria-label="`Remove one ${product.name}`"
                                    @click="
                                        setCount(product, countOf(product) - 1)
                                    "
                                >
                                    <Minus class="h-4 w-4" />
                                </Button>
                                <span
                                    class="w-10 text-center text-sm font-semibold tabular-nums"
                                    aria-live="polite"
                                    >{{ countOf(product) }}</span
                                >
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="h-11 w-11"
                                    :disabled="
                                        !product.in_stock ||
                                        countOf(product) >= product.max_count
                                    "
                                    :aria-label="`Add one ${product.name}`"
                                    @click="
                                        setCount(product, countOf(product) + 1)
                                    "
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                        </li>
                    </ul>

                    <p
                        v-if="form.errors.retail_items"
                        class="text-xs text-red-500"
                        role="alert"
                    >
                        {{ form.errors.retail_items }}
                    </p>
                </section>

                <!-- ── Customer, payment and total ───────── -->
                <aside class="space-y-5 lg:sticky lg:top-4 lg:self-start">
                    <section
                        class="space-y-3 rounded-xl border bg-card p-5"
                        aria-labelledby="customer-title"
                    >
                        <h2 id="customer-title" class="text-sm font-semibold">
                            Customer
                            <span class="font-normal text-muted-foreground"
                                >(optional)</span
                            >
                        </h2>

                        <div class="space-y-2">
                            <div
                                v-if="pickedCustomer"
                                class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-transparent dark:text-emerald-200"
                            >
                                <span
                                    ><span class="font-semibold">{{
                                        pickedCustomer.name
                                    }}</span>
                                    · {{ pickedCustomer.orders }}
                                    {{
                                        pickedCustomer.orders === 1
                                            ? 'order'
                                            : 'orders'
                                    }}</span
                                >
                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center rounded-lg border border-emerald-300 px-3 text-xs font-medium hover:bg-emerald-100"
                                    @click="clearPickedCustomer"
                                >
                                    Add as new instead
                                </button>
                            </div>
                            <CustomerSearch
                                v-else
                                input-id="sale-customer-search"
                                :search-url="customerSearchUrl"
                                @select="onCustomerPicked"
                            />
                            <p
                                v-if="form.errors.shop_customer_id"
                                class="text-xs text-red-500"
                                role="alert"
                            >
                                {{ form.errors.shop_customer_id }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label
                                for="customer-name"
                                class="text-sm font-medium"
                                >Name</label
                            >
                            <input
                                id="customer-name"
                                v-model="form.customer_name"
                                type="text"
                                autocomplete="off"
                                placeholder="Walk-in customer"
                                :readonly="!!pickedCustomer"
                                :class="[inputClass, 'read-only:bg-muted']"
                            />
                            <p
                                v-if="form.errors.customer_name"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.customer_name }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Phone</label>
                            <PhilippinePhoneInput
                                v-model="form.customer_phone"
                                :class="inputClass"
                            />
                            <p
                                v-if="form.errors.customer_phone"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label
                                for="customer-email"
                                class="text-sm font-medium"
                                >Email</label
                            >
                            <input
                                id="customer-email"
                                v-model="form.customer_email"
                                type="email"
                                autocomplete="off"
                                placeholder="customer@example.com"
                                :class="inputClass"
                            />
                            <p
                                v-if="form.errors.customer_email"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.customer_email }}
                            </p>
                        </div>
                    </section>

                    <section
                        class="space-y-3 rounded-xl border bg-card p-5"
                        aria-labelledby="payment-title"
                    >
                        <h2 id="payment-title" class="text-sm font-semibold">
                            Payment
                        </h2>

                        <div class="space-y-1">
                            <label
                                for="payment-method"
                                class="text-sm font-medium"
                                >Paid with</label
                            >
                            <select
                                id="payment-method"
                                v-model="form.payment_method"
                                :class="inputClass"
                            >
                                <option
                                    v-for="method in paymentMethods"
                                    :key="method"
                                    :value="method"
                                >
                                    {{ formatLabel(method) }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.payment_method"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.payment_method }}
                            </p>
                        </div>

                        <div v-if="isCash" class="space-y-1">
                            <label
                                for="amount-tendered"
                                class="text-sm font-medium"
                                >Cash received
                                <span class="font-normal text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <input
                                id="amount-tendered"
                                v-model="form.amount_tendered"
                                type="number"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                                :class="inputClass"
                            />
                            <p
                                v-if="cashShort"
                                class="text-xs text-red-500"
                                role="alert"
                            >
                                The cash received is less than the total.
                            </p>
                            <p
                                v-else-if="tendered !== null"
                                class="text-xs text-muted-foreground"
                            >
                                Change: {{ formatCurrency(change) }}
                            </p>
                            <p
                                v-if="form.errors.amount_tendered"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.amount_tendered }}
                            </p>
                        </div>
                    </section>

                    <section
                        class="space-y-2 rounded-xl border bg-card p-5"
                        aria-labelledby="total-title"
                    >
                        <h2 id="total-title" class="text-sm font-semibold">
                            Sale
                        </h2>

                        <p
                            v-if="picked.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            Pick at least one product.
                        </p>
                        <ul v-else class="space-y-1 text-sm">
                            <li
                                v-for="line in picked"
                                :key="line.product.id"
                                class="flex justify-between gap-3"
                            >
                                <span class="min-w-0 truncate"
                                    >{{ line.count }} ×
                                    {{ line.product.name }}</span
                                >
                                <span class="whitespace-nowrap">{{
                                    formatCurrency(line.amount)
                                }}</span>
                            </li>
                        </ul>

                        <div
                            class="space-y-1 border-t pt-2 text-sm"
                            v-if="picked.length"
                        >
                            <div
                                v-if="tax.label"
                                class="flex justify-between text-muted-foreground"
                            >
                                <span>{{ tax.label }}</span>
                                <span>{{ formatCurrency(taxAmount) }}</span>
                            </div>
                            <div
                                class="flex justify-between text-base font-semibold"
                            >
                                <span>Total</span>
                                <span>{{ formatCurrency(total) }}</span>
                            </div>
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 h-11 w-full gap-2"
                            :disabled="!canSubmit"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <Receipt v-else class="h-4 w-4" aria-hidden="true" />
                            Record sale and print receipt
                        </Button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
