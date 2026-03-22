import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string
    href: string
    icon?: any
    children?: NavItem[]
}

export type Permissions = Record<string, string[]>

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

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    order: Order | null;
};

export type BreadcrumbItemType = BreadcrumbItem;
