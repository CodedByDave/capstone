<script setup lang="ts">
import DriverLayout from '@/components/driver/DriverLayout.vue';
import { Button } from '@/components/ui/button';
import type { DriverInfo } from '@/types/driver';
import { router } from '@inertiajs/vue3';
import { LogOut, Phone, Star, Store, Truck } from 'lucide-vue-next';

defineProps<{ driver: DriverInfo; rating: number | null }>();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <DriverLayout title="Profile">
        <h1 class="text-xl font-bold">Profile</h1>

        <section class="mt-4 rounded-2xl bg-white p-5 text-center">
            <span
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-xl font-bold text-slate-700"
                aria-hidden="true"
            >
                {{ driver.name.charAt(0).toUpperCase() }}
            </span>
            <p class="mt-3 text-lg font-bold">{{ driver.name }}</p>
            <p class="text-sm text-slate-500">Driver</p>
        </section>

        <ul class="mt-3 divide-y rounded-2xl bg-white text-sm">
            <li class="flex items-center gap-3 p-4">
                <Store class="h-4 w-4 text-slate-500" aria-hidden="true" />
                <span class="text-slate-500">Shop</span>
                <span class="ml-auto font-medium">{{
                    driver.shop_name ?? '—'
                }}</span>
            </li>
            <li class="flex items-center gap-3 p-4">
                <Phone class="h-4 w-4 text-slate-500" aria-hidden="true" />
                <span class="text-slate-500">Phone</span>
                <span class="ml-auto font-medium">{{
                    driver.phone ?? '—'
                }}</span>
            </li>
            <li class="flex items-center gap-3 p-4">
                <Truck class="h-4 w-4 text-slate-500" aria-hidden="true" />
                <span class="text-slate-500">Vehicle</span>
                <span class="ml-auto font-medium">{{
                    driver.vehicle_type ?? '—'
                }}</span>
            </li>
            <li class="flex items-center gap-3 p-4">
                <Star class="h-4 w-4 text-slate-500" aria-hidden="true" />
                <span class="text-slate-500">Customer rating</span>
                <span class="ml-auto font-medium">{{ rating ?? '—' }}</span>
            </li>
        </ul>

        <Button
            variant="outline"
            class="mt-4 h-12 w-full rounded-xl"
            @click="logout"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Button>
    </DriverLayout>
</template>
