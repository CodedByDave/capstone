<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Home, RotateCcw, XCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment Cancelled', href: '/shop/payment/cancel' },
];
</script>

<template>
    <Head title="Payment Cancelled" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Payment Cancelled">
        <div class="flex min-h-[60vh] items-center justify-center p-4">
            <Card class="w-full max-w-md">
                <CardContent class="space-y-4 pt-8 text-center">
                    <div
                        class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                    >
                        <XCircle
                            class="h-10 w-10 text-red-600 dark:text-red-400"
                        />
                    </div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        Payment Cancelled
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Your payment was cancelled. No charges were made.
                    </p>
                    <div class="flex flex-col gap-3 pt-4 sm:flex-row">
                        <Link :href="'/shop/dashboard'" class="flex-1">
                            <Button variant="outline" class="w-full">
                                <Home class="mr-2 h-4 w-4" />
                                Return to Dashboard
                            </Button>
                        </Link>
                        <Link :href="'/shop/dashboard'" class="flex-1">
                            <Button class="w-full">
                                <RotateCcw class="mr-2 h-4 w-4" />
                                Try Again
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
