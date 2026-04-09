<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import type { BreadcrumbItem } from '@/types'
import {
    CheckCircle2,
    Package,
    User,
    Phone,
    MapPin,
    Clock,
    Download,
    Home,
    CreditCard,
    Users,
    Truck,
    DollarSign,
    UserCircle,
    BarChart3,
    Megaphone,
    CalendarDays,
    BadgeCheck
} from 'lucide-vue-next'
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps<{
    order?: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment Success', href: '/shop/payment/success' }
]

// -------------------- Barcode --------------------
const barcodeRef = ref<SVGElement | null>(null)

const generateBarcodeValue = () => {
    const key = `barcode_order_${props.order?.id ?? 'unknown'}`
    const existing = localStorage.getItem(key)
    if (existing) return existing
    const generated = String(Math.floor(1000000000 + Math.random() * 9000000000))
    localStorage.setItem(key, generated)
    return generated
}
const barcodeValue = ref(generateBarcodeValue())

onMounted(async () => {
    if (!props.order?.id) return
    await nextTick()
    try {
        const JsBarcode = (await import('jsbarcode')).default
        JsBarcode(barcodeRef.value, barcodeValue.value, {
            format: 'CODE128',
            lineColor: '#1f2937',
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 13,
            font: 'monospace',
            textAlign: 'center',
            textPosition: 'bottom',
            textMargin: 4,
            margin: 10,
            background: '#ffffff'
        })
    } catch (err) {
        console.error('Barcode generation failed:', err)
    }
})

// -------------------- Helpers --------------------
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

const getModuleIcon = (moduleName: string) => {
    const iconMap: Record<string, any> = {
        'CRM': Users,
        'Supply Chain': Truck,
        'Billing / Invoicing': DollarSign,
        'Employee Management': UserCircle,
        'Analytics / Reporting': BarChart3,
        'Marketing': Megaphone
    }
    return iconMap[moduleName] || Package
}

const billingLabel = (months: number) => {
    if (!months) return 'N/A'
    if (months === 1) return '1 Month'
    if (months === 12) return '1 Year (Annual)'
    return `${months} Months`
}

const isLoading = ref(false)

// -------------------- Unlock nav after payment --------------------
const unlockDashboard = () => {
    localStorage.setItem('shop_payment_verified', 'true')
}

// -------------------- PDF Download --------------------
const downloadReceipt = async () => {
    if (isLoading.value) return
    try {
        isLoading.value = true

        const { jsPDF } = await import('jspdf')
        const JsBarcode = (await import('jsbarcode')).default

        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
        let y = 20

        // Header
        doc.setFontSize(28)
        doc.setTextColor(31, 41, 55)
        doc.text('LAUNDRYHUB', 105, y, { align: 'center' })
        doc.setFontSize(18)
        doc.setTextColor(107, 114, 128)
        doc.text('Payment Receipt', 105, y + 10, { align: 'center' })
        doc.setFontSize(11)
        doc.setTextColor(156, 163, 175)
        doc.text(`Generated on ${new Date().toLocaleDateString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        })}`, 105, y + 18, { align: 'center' })
        y += 35

        // Barcode in PDF via canvas
        const canvas = document.createElement('canvas')
        JsBarcode(canvas, barcodeValue.value, {
            format: 'CODE128',
            lineColor: '#1f2937',
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 13,
            font: 'monospace',
            textAlign: 'center',
            textPosition: 'bottom',
            textMargin: 4,
            margin: 10,
            background: '#ffffff'
        })
        const barcodeDataUrl = canvas.toDataURL('image/png')
        doc.addImage(barcodeDataUrl, 'PNG', 65, y, 80, 25)
        y += 32

        // Invoice Details
        doc.setFontSize(13)
        doc.setTextColor(31, 41, 55)
        doc.setFont(undefined, 'bold')
        doc.text('Invoice Details', 20, y)
        doc.setDrawColor(107, 114, 128)
        doc.line(20, y + 2, 50, y + 2)
        y += 10

        doc.setFontSize(11)
        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)
        doc.text('Invoice Number:', 20, y)
        doc.setFont(undefined, 'bold')
        doc.setTextColor(31, 41, 55)
        doc.text(`INV-${String(props.order?.id || '000000').padStart(6, '0')}`, 60, y)
        y += 8

        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)
        doc.text('Barcode Ref:', 20, y)
        doc.setTextColor(31, 41, 55)
        doc.text(barcodeValue.value, 60, y)
        y += 8

        doc.text('Invoice Date:', 20, y)
        doc.text(formatDate(props.order?.created_at || ''), 60, y)
        y += 8

        doc.text('Payment Method:', 20, y)
        doc.text((props.order?.payment_method || 'N/A').toUpperCase(), 60, y)
        y += 8

        doc.text('Plan:', 20, y)
        doc.text(props.order?.plan_name || 'N/A', 60, y)
        y += 8

        doc.text('Billing Period:', 20, y)
        doc.text(billingLabel(props.order?.billing_months), 60, y)
        y += 8

        if (props.order?.expires_at) {
            doc.text('Expires At:', 20, y)
            doc.text(formatDate(props.order.expires_at), 60, y)
            y += 8
        }

        doc.text('Status:', 20, y)
        doc.setTextColor(22, 163, 74)
        doc.setFont(undefined, 'bold')
        doc.text('PAID', 60, y)
        y += 20

        // Shop Information
        doc.setFontSize(13)
        doc.setTextColor(31, 41, 55)
        doc.setFont(undefined, 'bold')
        doc.text('Shop Information', 20, y)
        doc.line(20, y + 2, 60, y + 2)
        y += 10

        doc.setFontSize(11)
        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)

        doc.text('Shop Name:', 20, y)
        doc.setFont(undefined, 'bold')
        doc.setTextColor(31, 41, 55)
        doc.text(props.order?.shop_name || 'N/A', 60, y)
        y += 8

        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)
        doc.text('Owner:', 20, y)
        doc.setFont(undefined, 'bold')
        doc.setTextColor(31, 41, 55)
        doc.text(props.order?.owner_name || 'N/A', 60, y)
        y += 8

        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)
        doc.text('Contact:', 20, y)
        doc.text(props.order?.phone || 'N/A', 60, y)
        y += 8

        doc.text('Email:', 20, y)
        doc.text(props.order?.email || 'N/A', 60, y)
        y += 15

        doc.text('Address:', 20, y)
        y += 8
        const street = props.order?.block_street ? `${props.order.block_street}, ` : ''
        doc.text(`${street}${props.order?.barangay || ''}, ${props.order?.municipality || ''} ${props.order?.postal_code || ''}`, 20, y)
        y += 25

        // Plan + Modules
        doc.setFontSize(13)
        doc.setTextColor(31, 41, 55)
        doc.setFont(undefined, 'bold')
        doc.text('Plan Purchased', 20, y)
        doc.line(20, y + 2, 55, y + 2)
        y += 12

        // Plan name row
        doc.setFontSize(12)
        doc.setTextColor(31, 41, 55)
        doc.setFont(undefined, 'bold')
        doc.text(`${props.order?.plan_name || 'N/A'} Plan`, 20, y)
        doc.setFontSize(11)
        doc.setTextColor(22, 163, 74)
        const total = parseFloat(props.order?.total_price || 0)
        doc.text(`PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`, 190, y, { align: 'right' })
        y += 6

        doc.setFontSize(9)
        doc.setFont(undefined, 'normal')
        doc.setTextColor(107, 114, 128)
        doc.text(`${billingLabel(props.order?.billing_months)} subscription`, 20, y)
        y += 10

        // Included modules list
        doc.setFontSize(10)
        doc.setFont(undefined, 'bold')
        doc.setTextColor(59, 130, 246)
        doc.text('Included Modules:', 20, y)
        y += 7

        doc.setFont(undefined, 'normal')
        doc.setTextColor(55, 65, 81)
        props.order?.modules?.forEach((module: any) => {
            doc.text(`• ${module.name}`, 24, y)
            y += 6
        })
        y += 12

        // Total
        doc.setDrawColor(200, 200, 200)
        doc.line(20, y, 190, y)
        y += 8

        doc.setFontSize(13)
        doc.setFont(undefined, 'bold')
        doc.setTextColor(31, 41, 55)
        doc.text('Total Paid:', 130, y)
        doc.setFontSize(16)
        doc.setTextColor(22, 163, 74)
        doc.text(`PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`, 190, y, { align: 'right' })
        y += 25

        doc.setFontSize(11)
        doc.setTextColor(22, 101, 52)
        doc.setFont(undefined, 'bold')
        doc.text('Payment Status: PAID', 20, y)
        doc.setFont(undefined, 'normal')
        doc.setTextColor(75, 85, 99)
        doc.text(`Paid on ${formatDate(props.order?.created_at || '')}`, 20, y + 6)
        y += 20

        doc.setFontSize(9)
        doc.setTextColor(156, 163, 175)
        doc.text('This is a computer-generated invoice and is valid without signature.', 105, y, { align: 'center' })
        doc.text(`LaundryHub Management System • © ${new Date().getFullYear()}`, 105, y + 5, { align: 'center' })

        doc.setProperties({
            title: `LaundryHub Receipt - Order ${props.order?.id || ''}`,
            subject: 'Payment Receipt',
            creator: 'LaundryHub',
            author: 'LaundryHub'
        })

        doc.save(`LaundryHub_Receipt_${props.order?.id || Date.now()}.pdf`)
        isLoading.value = false
    } catch (error) {
        console.error('Error generating PDF:', error)
        isLoading.value = false
        alert('Failed to generate PDF. Please try again.')
    }
}
</script>

<template>
    <Head title="Payment Successful" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Payment Success">
        <div class="p-4 space-y-6">

            <!-- Success Header -->
            <div class="text-center space-y-4 py-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 dark:bg-green-900/30 rounded-full">
                    <CheckCircle2 class="w-12 h-12 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment Successful!</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Thank you for your order. We've received your payment.</p>
                </div>
            </div>

            <!-- Order Details -->
            <div v-if="order" class="grid md:grid-cols-3 gap-6">

                <!-- Main Order Info -->
                <div class="md:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Order Summary</CardTitle>
                                <Badge class="bg-green-600">Paid</Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">

                            <!-- Barcode Section -->
                            <div class="flex flex-col items-center justify-center py-4 bg-white rounded-xl border">
                                <p class="text-xs text-gray-400 mb-2 tracking-widest uppercase font-medium">Order Barcode</p>
                                <svg ref="barcodeRef" class="max-w-full"></svg>
                            </div>

                            <!-- Order ID & Date -->
                            <div class="grid grid-cols-2 gap-4 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Order ID</p>
                                    <p class="font-mono font-semibold">#{{ order.id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Order Date</p>
                                    <p class="font-semibold">{{ formatDate(order.created_at) }}</p>
                                </div>
                            </div>

                            <!-- Shop Details -->
                            <div class="space-y-3">
                                <h3 class="font-semibold text-lg">Shop Information</h3>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                            <Package class="w-4 h-4" /> Shop Name
                                        </p>
                                        <p class="font-medium ml-6">{{ order.shop_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                            <User class="w-4 h-4" /> Owner
                                        </p>
                                        <p class="font-medium ml-6">{{ order.owner_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                            <Phone class="w-4 h-4" /> Contact
                                        </p>
                                        <p class="font-medium ml-6">{{ order.phone }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                            <MapPin class="w-4 h-4" /> Email
                                        </p>
                                        <p class="font-medium ml-6">{{ order.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="pt-4 border-t">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 flex items-center gap-2">
                                    <MapPin class="w-4 h-4" /> Shop Address
                                </p>
                                <p class="font-medium ml-6">
                                    <template v-if="order.block_street">{{ order.block_street }}, </template>
                                    {{ order.barangay }}<br>
                                    {{ order.municipality }}<template v-if="order.postal_code">, {{ order.postal_code }}</template>
                                </p>
                            </div>

                            <!-- Plan + Included Modules -->
                            <div class="pt-4 border-t">
                                <h3 class="font-semibold text-lg mb-3">Plan Purchased</h3>

                                <!-- Plan header -->
                                <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                                <BadgeCheck class="w-5 h-5 text-white" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-blue-900 dark:text-blue-100 text-base">{{ order.plan_name }} Plan</p>
                                                <p class="text-xs text-blue-600 dark:text-blue-400">{{ billingLabel(order.billing_months) }} subscription</p>
                                            </div>
                                        </div>
                                        <p class="font-bold text-green-600 text-lg">
                                            ₱{{ parseFloat(order.total_price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                                        </p>
                                    </div>

                                    <!-- Included sub-modules -->
                                    <div class="border-t border-blue-200 dark:border-blue-700 pt-3 mt-1">
                                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-widest mb-2">Included Modules</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div v-for="(module, index) in order.modules" :key="index"
                                                class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
                                                <component :is="getModuleIcon(module.name)" class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                                <span>{{ module.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="pt-4 border-t">
                                <div class="flex items-center justify-between text-xl">
                                    <span class="font-semibold">Total Paid</span>
                                    <span class="font-bold text-green-600">₱{{ parseFloat(order.total_price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                                </div>
                            </div>

                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">

                    <!-- Payment Info (from orders table) -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Payment Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" /> Payment Method
                                </p>
                                <p class="font-medium ml-6 capitalize">{{ order.payment_method ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <BadgeCheck class="w-4 h-4" /> Status
                                </p>
                                <div class="ml-6">
                                    <Badge class="bg-green-600 capitalize">{{ order.status }}</Badge>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <Package class="w-4 h-4" /> Plan
                                </p>
                                <p class="font-medium ml-6">{{ order.plan_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <CalendarDays class="w-4 h-4" /> Billing Period
                                </p>
                                <p class="font-medium ml-6">{{ billingLabel(order.billing_months) }}</p>
                            </div>
                            <div v-if="order.expires_at">
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <Clock class="w-4 h-4" /> Expires At
                                </p>
                                <p class="font-medium text-sm ml-6">{{ formatDate(order.expires_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                    <Clock class="w-4 h-4" /> Paid At
                                </p>
                                <p class="font-medium text-sm ml-6">{{ formatDate(order.created_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Next Steps -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">What's Next?</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <Clock class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm">Wait for admin approval</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Your plan will be automatically activated when admin approves your order. Thank you!</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardContent class="pt-6 space-y-3">
                            <Button @click="downloadReceipt" class="w-full" :disabled="isLoading">
                                <Download class="w-4 h-4 mr-2" />
                                {{ isLoading ? 'Generating PDF...' : 'Download Receipt (PDF)' }}
                            </Button>
                            <Link :href="'/shop/dashboard'" @click="unlockDashboard">
                                <Button variant="outline" class="w-full">
                                    <Home class="w-4 h-4 mr-2" />
                                    Return to Dashboard
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>

                </div>
            </div>

            <!-- No Order Fallback -->
            <Card v-else>
                <CardContent class="py-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                        <CheckCircle2 class="w-10 h-10 text-green-600 dark:text-green-400" />
                    </div>
                    <h2 class="text-xl font-semibold mb-2">Payment Received</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Your payment was successful. Check your email for details.</p>
                    <Link :href="'/shop/dashboard'" @click="unlockDashboard">
                        <Button>
                            <Home class="w-4 h-4 mr-2" />
                            Return to Dashboard
                        </Button>
                    </Link>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
