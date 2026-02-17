<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import type { BreadcrumbItem } from '@/types'
import { XCircle, Home, RotateCcw } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment Cancelled', href: '/shop/payment/cancel' }
]
</script>

<template>
<Head title="Payment Cancelled" />

<ShopLayout :breadcrumbs="breadcrumbs" title="Payment Cancelled">
    <div class="flex items-center justify-center min-h-[60vh] p-4">
        <Card class="max-w-md w-full">
            <CardContent class="pt-8 text-center space-y-4">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <XCircle class="w-10 h-10 text-red-600 dark:text-red-400" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Payment Cancelled
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Your payment was cancelled. No charges were made.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <Link :href="'/shop/dashboard'" class="flex-1">
                        <Button variant="outline" class="w-full">
                            <Home class="w-4 h-4 mr-2" />
                            Return to Dashboard
                        </Button>
                    </Link>
                    <Link :href="'/shop/dashboard'" class="flex-1">
                        <Button class="w-full">
                            <RotateCcw class="w-4 h-4 mr-2" />
                            Try Again
                        </Button>
                    </Link>
                </div>
            </CardContent>
        </Card>
    </div>
</ShopLayout>
</template>
