export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
    children?: NavItem[];
}

export type Permissions = Record<string, string[]>;

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role: 'owner' | 'staff' | 'super_admin' | 'user';
    permissions: Permissions;
}

export interface Order {
    status: string;
    modules: OrderModule[];
}

export interface OrderModule {
    name: string;
    price: number;
}

export interface ShopCapabilities {
    setup_completed: boolean;
    multiple_locations: boolean;
    offers_pickup: boolean;
    offers_delivery: boolean;
    /** The shop delivers but has not set its delivery pricing yet. */
    delivery_pricing_required?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    order: Order | null;
    shopCapabilities?: ShopCapabilities | null;
    /** Owners only: the server wants them to rate LaundryHub now. */
    platformRatingPrompt?: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;
