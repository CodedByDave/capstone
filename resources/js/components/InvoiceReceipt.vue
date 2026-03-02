<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Building2,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Hash,
    CreditCard,
    CheckCircle
} from 'lucide-vue-next'

const props = defineProps<{
    order: any
    payment: any
}>()

const currentDate = new Date().toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
})

const formatDate = (date: string) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const subtotal = computed(() => props.order.total_price)
const tax = computed(() => 0) // No tax for now
const total = computed(() => subtotal.value + tax.value)
</script>

<template>
<div class="invoice-wrapper bg-white dark:bg-neutral-900 p-8 max-w-4xl mx-auto">
    <div class="invoice-content">

        <!-- Header -->
        <div class="invoice-header border-b-2 border-gray-900 dark:border-gray-100 pb-8 mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">INVOICE</h1>
                    <p class="text-gray-600 dark:text-gray-400">Payment Receipt</p>
                </div>
                <div class="text-right">
                    <div class="mb-4">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">LaundryHub</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Laundry Management System</p>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p>support@laundryhub.com</p>
                        <p>+63 123 456 7890</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Bill To -->
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Bill To</h3>
                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <Building2 class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ order.shop_name }}</p>
                            <p v-if="order.branch_name && order.branch_name !== 'N/A'" class="text-sm text-gray-600 dark:text-gray-400">
                                Branch: {{ order.branch_name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <Mail class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.email }}</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <Phone class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.phone }}</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <MapPin class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ order.neighborhood }}, {{ order.barangay }}<br>
                            {{ order.municipality }}, {{ order.postal_code }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Invoice Details</h3>
                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <Hash class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Invoice Number</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">INV-{{ String(order.id).padStart(6, '0') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <Calendar class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Invoice Date</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <CreditCard class="w-4 h-4 text-gray-400 mt-1 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Payment Method</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ payment.payment_method }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <CheckCircle class="w-4 h-4 text-green-600 mt-1 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Payment Status</p>
                            <p class="text-sm font-semibold text-green-600">PAID</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-900 dark:border-gray-100">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Description</th>
                        <th class="text-center py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Qty</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Unit Price</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(module, index) in order.modules"
                        :key="index"
                        class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-4 px-4">
                            <p class="font-medium text-gray-900 dark:text-white">{{ module.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Module License - Annual Subscription</p>
                        </td>
                        <td class="text-center py-4 px-4 text-gray-900 dark:text-white">1</td>
                        <td class="text-right py-4 px-4 text-gray-900 dark:text-white">₱{{ module.price.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</td>
                        <td class="text-right py-4 px-4 font-medium text-gray-900 dark:text-white">₱{{ module.price.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-full md:w-1/2 lg:w-1/3">
                <div class="space-y-2">
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="font-medium text-gray-900 dark:text-white">₱{{ subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Tax (0%):</span>
                        <span class="font-medium text-gray-900 dark:text-white">₱{{ tax.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="flex justify-between py-3 border-t-2 border-gray-900 dark:border-gray-100">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                        <span class="text-2xl font-bold text-green-600">₱{{ total.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 mt-2">
                        <p class="text-sm font-semibold text-green-800 dark:text-green-400 flex items-center gap-2">
                            <CheckCircle class="w-4 h-4" />
                            Paid on {{ formatDate(payment.paid_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Notes</h3>
            <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4 space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    • Thank you for choosing LaundryHub! Your modules are being prepared for deployment.
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    • Our team will contact you within 24-48 hours to schedule your system setup and training.
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    • You will receive login credentials and access instructions via email.
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    • For support, contact us at support@laundryhub.com or call +63 123 456 7890
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 dark:border-gray-700 mt-8 pt-8 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                This is a computer-generated invoice and is valid without signature.
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                LaundryHub Management System • Powered by Innovation • © {{ new Date().getFullYear() }}
            </p>
        </div>

    </div>
</div>
</template>

<style scoped>
/* Print Styles */
@media print {
    .invoice-wrapper {
        background: white !important;
        padding: 0 !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .invoice-content {
        color: black !important;
    }

    .dark\:bg-neutral-900,
    .dark\:bg-neutral-800,
    .dark\:border-gray-700,
    .dark\:text-white,
    .dark\:text-gray-400 {
        background: white !important;
        color: black !important;
        border-color: #e5e7eb !important;
    }

    .text-blue-600,
    .dark\:text-blue-400 {
        color: #2563eb !important;
    }

    .text-green-600 {
        color: #16a34a !important;
    }

    .bg-green-50,
    .dark\:bg-green-900\/20 {
        background-color: #f0fdf4 !important;
    }

    .border-green-200,
    .dark\:border-green-800 {
        border-color: #bbf7d0 !important;
    }

    .text-green-800,
    .dark\:text-green-400 {
        color: #166534 !important;
    }

    /* Hide icons in print for cleaner look */
    svg {
        display: none;
    }
}

/* Screen Styles */
@media screen {
    .invoice-wrapper {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
}
</style>
