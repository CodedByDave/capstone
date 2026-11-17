<script setup lang="ts">
import { debounce } from 'lodash';
import { Search, Users } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

/**
 * A search bar for the shop's existing customers, used at the top of an order form. Staff search by
 * name, phone or email and pick the customer, so the order is linked to that exact record instead
 * of being matched by whatever name is typed. A customer who is not found is added by filling in
 * the details instead.
 */
export interface CustomerSuggestion {
    public_id: string;
    name: string;
    phone: string | null;
    email: string | null;
    orders: number;
}

const props = defineProps<{
    /** Where to search, e.g. `/shop/operations/customers/search`. */
    searchUrl: string;
    inputId?: string;
}>();

const emit = defineEmits<{ select: [customer: CustomerSuggestion] }>();

const query = ref('');
const suggestions = ref<CustomerSuggestion[]>([]);
const open = ref(false);
const searched = ref(false);
const active = ref(-1);
const listId = `customer-search-${Math.random().toString(36).slice(2, 8)}`;
let requestId = 0;

const showList = computed(() => open.value && query.value.trim().length >= 2);

const search = debounce(async (term: string) => {
    const current = ++requestId;

    if (term.trim().length < 2) {
        suggestions.value = [];
        searched.value = false;
        return;
    }

    try {
        const response = await fetch(
            `${props.searchUrl}?q=${encodeURIComponent(term.trim())}`,
            {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            },
        );

        // A slower, older answer must not replace a newer one.
        if (!response.ok || current !== requestId) return;

        suggestions.value = (await response.json()).customers ?? [];
        searched.value = true;
        active.value = -1;
    } catch {
        suggestions.value = [];
    }
}, 250);

onBeforeUnmount(() => search.cancel());

function onInput() {
    open.value = true;
    search(query.value);
}

function pick(customer: CustomerSuggestion) {
    emit('select', customer);
    query.value = '';
    suggestions.value = [];
    searched.value = false;
    open.value = false;
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        open.value = false;
        return;
    }

    if (!suggestions.value.length) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        active.value = (active.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        active.value =
            (active.value - 1 + suggestions.value.length) %
            suggestions.value.length;
    } else if (event.key === 'Enter' && active.value >= 0) {
        event.preventDefault();
        pick(suggestions.value[active.value]);
    }
}

// Clicking a suggestion must register before the field loses focus and closes the list.
function onBlur() {
    window.setTimeout(() => (open.value = false), 150);
}
</script>

<template>
    <div class="relative">
        <label :for="inputId" class="sr-only">Search existing customers</label>
        <Search
            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            aria-hidden="true"
        />
        <input
            :id="inputId"
            v-model="query"
            type="search"
            role="combobox"
            autocomplete="off"
            placeholder="Search existing customer by name, phone or email…"
            :aria-expanded="showList"
            :aria-controls="listId"
            aria-autocomplete="list"
            class="h-11 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
            @input="onInput"
            @keydown="onKeydown"
            @focus="open = true"
            @blur="onBlur"
        />

        <ul
            v-if="showList && suggestions.length"
            :id="listId"
            role="listbox"
            class="absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-lg border bg-popover text-popover-foreground shadow-md"
        >
            <li
                v-for="(customer, index) in suggestions"
                :key="customer.public_id"
                role="option"
                :aria-selected="index === active"
            >
                <button
                    type="button"
                    class="flex min-h-11 w-full items-center gap-3 px-3 py-2 text-left text-sm hover:bg-accent focus-visible:bg-accent focus-visible:outline-none"
                    :class="{ 'bg-accent': index === active }"
                    @mousedown.prevent="pick(customer)"
                >
                    <Users
                        class="h-4 w-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium">{{
                            customer.name
                        }}</span>
                        <span
                            class="block truncate text-xs text-muted-foreground"
                        >
                            {{ customer.phone || 'No phone' }}
                            <template v-if="customer.email">
                                · {{ customer.email }}</template
                            >
                        </span>
                    </span>
                    <span class="shrink-0 text-xs text-muted-foreground">
                        {{ customer.orders }}
                        {{ customer.orders === 1 ? 'order' : 'orders' }}
                    </span>
                </button>
            </li>
        </ul>

        <p
            v-else-if="showList && searched"
            class="absolute z-20 mt-1 w-full rounded-lg border bg-popover px-3 py-3 text-sm text-muted-foreground shadow-md"
            role="status"
        >
            No customer found. Fill in the details below to add them.
        </p>
    </div>
</template>
