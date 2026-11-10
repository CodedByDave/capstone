import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface ShopCapabilities {
    setup_completed: boolean;
    offers_pickup: boolean;
    offers_delivery: boolean;
    has_suppliers: boolean;
}

/** The features this shop turned on in Operations Setup, shared by the backend on every page. */
export function useShopCapabilities() {
    const page = usePage();

    const capabilities = computed<Partial<ShopCapabilities>>(
        () =>
            ((page.props as Record<string, unknown>).shopCapabilities ??
                {}) as Partial<ShopCapabilities>,
    );

    const hasSuppliers = computed(
        () => capabilities.value.has_suppliers === true,
    );

    return { capabilities, hasSuppliers };
}
