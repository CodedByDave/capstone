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
    Mail,
    Clock,
    Rocket,
    Download,
    Home,
    CreditCard,
    Users,
    Truck,
    DollarSign,
    UserCircle,
    BarChart3,
    Megaphone
} from 'lucide-vue-next'

// Import jsPDF
import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'

const props = defineProps<{
    order?: any
    payment?: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment Success', href: '/shop/payment/success' }
]

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

// Map module names to icons
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

// Function to generate and download PDF receipt
const downloadReceipt = async () => {
    const receiptElement = document.getElementById('receipt-content')

    if (!receiptElement) {
        console.error('Receipt element not found')
        return
    }

    try {
        // Show loading state
        const originalText = document.querySelector('.download-btn-text')?.textContent
        const downloadBtn = document.querySelector('.download-btn')
        if (downloadBtn) {
            downloadBtn.innerHTML = '<span class="download-btn-text">Generating PDF...</span>'
            downloadBtn.setAttribute('disabled', 'true')
        }

        // Add print styles temporarily
        const originalStyles = receiptElement.getAttribute('style') || ''
        receiptElement.style.backgroundColor = 'white'
        receiptElement.style.color = 'black'
        receiptElement.style.padding = '20px'
        receiptElement.style.maxWidth = '800px'
        receiptElement.style.margin = '0 auto'

        // Hide buttons and other non-receipt elements
        const buttons = receiptElement.querySelectorAll('button, .no-print, .print-hidden')
        buttons.forEach(btn => {
            (btn as HTMLElement).style.display = 'none'
        })

        // Use html2canvas to capture the receipt
        const canvas = await html2canvas(receiptElement, {
            scale: 2, // Higher quality
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        })

        // Restore original styles
        receiptElement.setAttribute('style', originalStyles)
        buttons.forEach(btn => {
            (btn as HTMLElement).style.display = ''
        })

        // Create PDF
        const imgData = canvas.toDataURL('image/png')
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        })

        const pdfWidth = pdf.internal.pageSize.getWidth()
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight)

        // Add receipt metadata
        pdf.setProperties({
            title: `Receipt_${props.order?.id || 'UNKNOWN'}`,
            subject: 'Payment Receipt',
            creator: 'LaundryHub',
            author: 'LaundryHub'
        })

        // Generate filename
        const filename = `LaundryHub_Receipt_${props.order?.id || Date.now()}.pdf`

        // Download PDF
        pdf.save(filename)

        // Restore button text
        if (downloadBtn && originalText) {
            downloadBtn.innerHTML = `<span class="download-btn-text">${originalText}</span>`
            downloadBtn.removeAttribute('disabled')
        }

    } catch (error) {
        console.error('Error generating PDF:', error)
        alert('Failed to generate PDF. Please try again.')

        // Restore button
        const downloadBtn = document.querySelector('.download-btn')
        if (downloadBtn) {
            downloadBtn.innerHTML = '<span class="download-btn-text">Download Receipt</span>'
            downloadBtn.removeAttribute('disabled')
        }
    }
}

// Fallback print function
const printReceipt = () => {
    window.print()
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Payment Successful!
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Thank you for your order. We've received your payment.
                </p>
            </div>
        </div>

        <!-- Order Details (if available) -->
        <div v-if="order" class="grid md:grid-cols-3 gap-6">

            <!-- Main Order Info -->
            <div class="md:col-span-2 space-y-6">
                <Card id="receipt-content">
                    <!-- Receipt Content -->
                    <div class="print-hidden">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Order Summary</CardTitle>
                                <Badge class="bg-green-600">Paid</Badge>
                            </div>
                        </CardHeader>
                    </div>

                    <CardContent class="space-y-4">

                        <!-- Company Header for PDF -->
                        <div class="print-only mb-8 text-center border-b pb-4">
                            <h1 class="text-3xl font-bold text-gray-900">LaundryHub</h1>
                            <p class="text-gray-600">Payment Receipt</p>
                            <p class="text-sm text-gray-500 mt-2">Generated on {{ new Date().toLocaleDateString('en-PH', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}</p>
                        </div>

                        <!-- Order ID -->
                        <div class="grid grid-cols-2 gap-4 pb-4 border-b">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800">Order ID</p>
                                <p class="font-mono font-semibold print:text-gray-900">#{{ order.id }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800">Order Date</p>
                                <p class="font-semibold print:text-gray-900">{{ formatDate(order.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Shop Details -->
                        <div class="space-y-3">
                            <h3 class="font-semibold text-lg print:text-gray-900">Shop Information</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800 flex items-center gap-2">
                                        <Package class="w-4 h-4 print:hidden" />
                                        Shop Name
                                    </p>
                                    <p class="font-medium ml-6 print:ml-0 print:text-gray-900">{{ order.shop_name }}</p>
                                </div>
                                <div v-if="order.branch_name && order.branch_name !== 'N/A'">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800 flex items-center gap-2">
                                        <Package class="w-4 h-4 print:hidden" />
                                        Branch
                                    </p>
                                    <p class="font-medium ml-6 print:ml-0 print:text-gray-900">{{ order.branch_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800 flex items-center gap-2">
                                        <User class="w-4 h-4 print:hidden" />
                                        Owner
                                    </p>
                                    <p class="font-medium ml-6 print:ml-0 print:text-gray-900">{{ order.owner_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800 flex items-center gap-2">
                                        <Phone class="w-4 h-4 print:hidden" />
                                        Contact
                                    </p>
                                    <p class="font-medium ml-6 print:ml-0 print:text-gray-900">{{ order.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="pt-4 border-t">
                            <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800 mb-2 flex items-center gap-2">
                                <MapPin class="w-4 h-4 print:hidden" />
                                Shop Address
                            </p>
                            <p class="font-medium ml-6 print:ml-0 print:text-gray-900">
                                {{ order.neighborhood }}, {{ order.barangay }}<br>
                                {{ order.municipality }}, {{ order.postal_code }}
                            </p>
                        </div>

                        <!-- Modules -->
                        <div class="pt-4 border-t">
                            <h3 class="font-semibold text-lg mb-3 print:text-gray-900">Modules Purchased</h3>
                            <div class="space-y-2">
                                <div v-for="(module, index) in order.modules"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-neutral-800 print:bg-gray-100 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 print:bg-blue-100 rounded-lg flex items-center justify-center print-hidden">
                                            <component :is="getModuleIcon(module.name)" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <p class="font-semibold print:text-gray-900">{{ module.name }}</p>
                                    </div>
                                    <p class="font-bold text-green-600 print:text-green-800">₱{{ module.price.toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="pt-4 border-t">
                            <div class="flex items-center justify-between text-xl">
                                <span class="font-semibold print:text-gray-900">Total Paid</span>
                                <span class="font-bold text-green-600 print:text-green-800">₱{{ order.total_price.toLocaleString() }}</span>
                            </div>
                        </div>

                        <!-- Payment Details for PDF -->
                        <div class="pt-4 border-t print:block">
                            <h3 class="font-semibold text-lg mb-2 print:text-gray-900">Payment Details</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800">Payment Method</p>
                                    <p class="font-medium capitalize print:text-gray-900">{{ payment?.payment_method || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800">Status</p>
                                    <p class="font-medium text-green-600 print:text-green-800">{{ payment?.status || 'Paid' }}</p>
                                </div>
                                <div v-if="payment?.paid_at" class="col-span-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 print:text-gray-800">Paid At</p>
                                    <p class="font-medium text-sm print:text-gray-900">{{ formatDate(payment.paid_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Notes for PDF -->
                        <div class="pt-4 border-t print:block">
                            <p class="text-sm text-gray-500 print:text-gray-600 mb-2">
                                This is a computer-generated receipt and is valid without signature.
                            </p>
                            <p class="text-xs text-gray-400 print:text-gray-500">
                                LaundryHub Management System • © {{ new Date().getFullYear() }}
                            </p>
                        </div>

                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 print-hidden">

                <!-- Payment Info -->
                <Card v-if="payment">
                    <CardHeader>
                        <CardTitle class="text-lg">Payment Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <CreditCard class="w-4 h-4" />
                                Payment Method
                            </p>
                            <p class="font-medium ml-6 capitalize">{{ payment.payment_method }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <div class="ml-6">
                                <Badge class="bg-green-600">{{ payment.status }}</Badge>
                            </div>
                        </div>
                        <div v-if="payment.paid_at">
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <Clock class="w-4 h-4" />
                                Paid At
                            </p>
                            <p class="font-medium text-sm ml-6">{{ formatDate(payment.paid_at) }}</p>
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
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <Mail class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Check Your Email</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Confirmation sent to {{ order.email }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <Clock class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Setup Process</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    We'll contact you within 24-48 hours
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <Rocket class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Get Started</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    You'll receive login credentials soon
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <Card>
                    <CardContent class="pt-6 space-y-3">
                        <Button @click="downloadReceipt" variant="outline" class="w-full download-btn">
                            <Download class="w-4 h-4 mr-2" />
                            <span class="download-btn-text">Download Receipt (PDF)</span>
                        </Button>

                        <Button @click="printReceipt" variant="outline" class="w-full">
                            <Download class="w-4 h-4 mr-2" />
                            Print Receipt
                        </Button>

                        <Link :href="'/shop/dashboard'">
                            <Button class="w-full">
                                <Home class="w-4 h-4 mr-2" />
                                Return to Dashboard
                            </Button>
                        </Link>
                    </CardContent>
                </Card>

            </div>

        </div>

        <!-- No Order Data Fallback -->
        <Card v-else>
            <CardContent class="py-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                    <CheckCircle2 class="w-10 h-10 text-green-600 dark:text-green-400" />
                </div>
                <h2 class="text-xl font-semibold mb-2">Payment Received</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Your payment was successful. Check your email for details.
                </p>
                <Link :href="'/shop/dashboard'">
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

<style scoped>
/* Print Styles */
@media print {
    .print-hidden {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    body * {
        visibility: hidden;
    }

    #receipt-content, #receipt-content * {
        visibility: visible;
    }

    #receipt-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
        background: white !important;
    }

    /* Ensure proper text colors for print */
    .print\:text-gray-900 {
        color: #111827 !important;
    }

    .print\:text-gray-800 {
        color: #1f2937 !important;
    }

    .print\:text-gray-600 {
        color: #4b5563 !important;
    }

    .print\:text-gray-500 {
        color: #6b7280 !important;
    }

    .print\:text-green-800 {
        color: #166534 !important;
    }

    .print\:bg-gray-100 {
        background-color: #f3f4f6 !important;
    }

    .print\:bg-blue-100 {
        background-color: #dbeafe !important;
    }

    button, .no-print {
        display: none !important;
    }
}

/* Screen Styles */
.print-only {
    display: none;
}
</style>
