<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    BarChart3,
    CalendarDays,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Download,
    Home,
    MapPin,
    Megaphone,
    Package,
    Phone,
    Truck,
    User,
    UserCircle,
    Users,
} from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<{
    order?: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment Success', href: '#' },
];

// -------------------- Barcode --------------------
const barcodeRef = ref<SVGElement | null>(null);

const barcodeValue = ref(
    props.order?.transaction_reference ?? props.order?.public_id ?? 'UNKNOWN',
);

onMounted(async () => {
    if (!props.order?.public_id) return;
    await nextTick();
    try {
        const JsBarcode = (await import('jsbarcode')).default;
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
            background: '#ffffff',
        });
    } catch (err) {
        console.error('Barcode generation failed:', err);
    }
});

// -------------------- Helpers --------------------
const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getModuleIcon = (moduleName: string) => {
    const iconMap: Record<string, any> = {
        CRM: Users,
        'Supply Chain': Truck,
        'Billing / Invoicing': DollarSign,
        'Employee Management': UserCircle,
        'Analytics / Reporting': BarChart3,
        Marketing: Megaphone,
    };
    return iconMap[moduleName] || Package;
};

const billingLabel = (months: number) => {
    if (!months) return 'N/A';
    if (months === 1) return '1 Month';
    if (months === 12) return '1 Year (Annual)';
    return `${months} Months`;
};

const isLoading = ref(false);

// -------------------- Unlock nav after payment --------------------
const unlockDashboard = () => {
    localStorage.setItem('shop_payment_verified', 'true');
};

// -------------------- PDF Download --------------------
const downloadReceipt = async () => {
    if (isLoading.value) return;
    try {
        isLoading.value = true;

        const { jsPDF } = await import('jspdf');
        const JsBarcode = (await import('jsbarcode')).default;

        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
        });
        let y = 20;

        // Header
        doc.setFontSize(28);
        doc.setTextColor(31, 41, 55);
        doc.text('LAUNDRYHUB', 105, y, { align: 'center' });
        doc.setFontSize(18);
        doc.setTextColor(107, 114, 128);
        doc.text('Payment Receipt', 105, y + 10, { align: 'center' });
        doc.setFontSize(11);
        doc.setTextColor(156, 163, 175);
        doc.text(
            `Generated on ${new Date().toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            })}`,
            105,
            y + 18,
            { align: 'center' },
        );
        y += 35;

        // Barcode in PDF via canvas
        const canvas = document.createElement('canvas');
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
            background: '#ffffff',
        });
        const barcodeDataUrl = canvas.toDataURL('image/png');
        doc.addImage(barcodeDataUrl, 'PNG', 65, y, 80, 25);
        y += 32;

        // Invoice Details
        doc.setFontSize(13);
        doc.setTextColor(31, 41, 55);
        doc.setFont(undefined, 'bold');
        doc.text('Invoice Details', 20, y);
        doc.setDrawColor(107, 114, 128);
        doc.line(20, y + 2, 50, y + 2);
        y += 10;

        doc.setFontSize(11);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);
        doc.text('Invoice Number:', 20, y);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text(props.order?.transaction_reference || 'UNKNOWN', 60, y);
        y += 8;

        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);
        doc.text('Barcode Ref:', 20, y);
        doc.setTextColor(31, 41, 55);
        doc.text(barcodeValue.value, 60, y);
        y += 8;

        doc.text('Invoice Date:', 20, y);
        doc.text(formatDate(props.order?.created_at || ''), 60, y);
        y += 8;

        doc.text('Payment Method:', 20, y);
        doc.text((props.order?.payment_method || 'N/A').toUpperCase(), 60, y);
        y += 8;

        doc.text('Plan:', 20, y);
        doc.text(props.order?.plan_name || 'N/A', 60, y);
        y += 8;

        doc.text('Billing Period:', 20, y);
        doc.text(billingLabel(props.order?.billing_months), 60, y);
        y += 8;

        if (props.order?.expires_at) {
            doc.text('Expires At:', 20, y);
            doc.text(formatDate(props.order.expires_at), 60, y);
            y += 8;
        }

        doc.text('Status:', 20, y);
        doc.setTextColor(22, 163, 74);
        doc.setFont(undefined, 'bold');
        doc.text('PAID', 60, y);
        y += 20;

        // Shop Information
        doc.setFontSize(13);
        doc.setTextColor(31, 41, 55);
        doc.setFont(undefined, 'bold');
        doc.text('Shop Information', 20, y);
        doc.line(20, y + 2, 60, y + 2);
        y += 10;

        doc.setFontSize(11);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);

        doc.text('Shop Name:', 20, y);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text(props.order?.shop_name || 'N/A', 60, y);
        y += 8;

        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);
        doc.text('Owner:', 20, y);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text(props.order?.owner_name || 'N/A', 60, y);
        y += 8;

        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);
        doc.text('Contact:', 20, y);
        doc.text(props.order?.phone || 'N/A', 60, y);
        y += 8;

        doc.text('Email:', 20, y);
        doc.text(props.order?.email || 'N/A', 60, y);
        y += 15;

        doc.text('Address:', 20, y);
        y += 8;
        const street = props.order?.block_street
            ? `${props.order.block_street}, `
            : '';
        doc.text(
            `${street}${props.order?.barangay || ''}, ${props.order?.municipality || ''} ${props.order?.postal_code || ''}`,
            20,
            y,
        );
        y += 25;

        // Plan + Modules
        doc.setFontSize(13);
        doc.setTextColor(31, 41, 55);
        doc.setFont(undefined, 'bold');
        doc.text('Plan Purchased', 20, y);
        doc.line(20, y + 2, 55, y + 2);
        y += 12;

        // Plan name row
        doc.setFontSize(12);
        doc.setTextColor(31, 41, 55);
        doc.setFont(undefined, 'bold');
        doc.text(`${props.order?.plan_name || 'N/A'} Plan`, 20, y);
        doc.setFontSize(11);
        doc.setTextColor(22, 163, 74);
        const total = parseFloat(props.order?.total_price || 0);
        doc.text(
            `PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
            190,
            y,
            { align: 'right' },
        );
        y += 6;

        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(107, 114, 128);
        doc.text(
            `${billingLabel(props.order?.billing_months)} subscription`,
            20,
            y,
        );
        y += 10;

        // Included modules list
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(59, 130, 246);
        doc.text('Included Modules:', 20, y);
        y += 7;

        doc.setFont(undefined, 'normal');
        doc.setTextColor(55, 65, 81);
        props.order?.modules?.forEach((module: any) => {
            doc.text(`• ${module.name}`, 24, y);
            y += 6;
        });
        y += 12;

        // Total
        doc.setDrawColor(200, 200, 200);
        doc.line(20, y, 190, y);
        y += 8;

        doc.setFontSize(13);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text('Total Paid:', 130, y);
        doc.setFontSize(16);
        doc.setTextColor(22, 163, 74);
        doc.text(
            `PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
            190,
            y,
            { align: 'right' },
        );
        y += 25;

        doc.setFontSize(11);
        doc.setTextColor(22, 101, 52);
        doc.setFont(undefined, 'bold');
        doc.text('Payment Status: PAID', 20, y);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(75, 85, 99);
        doc.text(
            `Paid on ${formatDate(props.order?.created_at || '')}`,
            20,
            y + 6,
        );
        y += 20;

        doc.setFontSize(9);
        doc.setTextColor(156, 163, 175);
        doc.text(
            'This is a computer-generated invoice and is valid without signature.',
            105,
            y,
            { align: 'center' },
        );
        doc.text(
            `LaundryHub Management System • © ${new Date().getFullYear()}`,
            105,
            y + 5,
            { align: 'center' },
        );

        doc.setProperties({
            title: `LaundryHub Receipt - ${props.order?.transaction_reference || ''}`,
            subject: 'Payment Receipt',
            creator: 'LaundryHub',
            author: 'LaundryHub',
        });

        doc.save(
            `LaundryHub_Receipt_${props.order?.transaction_reference || Date.now()}.pdf`,
        );
        isLoading.value = false;
    } catch (error) {
        console.error('Error generating PDF:', error);
        isLoading.value = false;
        alert('Failed to generate PDF. Please try again.');
    }
};
</script>

<template>
    <Head title="Payment Successful" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Payment Success">
        <div class="space-y-6 p-4">
            <!-- Success Header -->
            <div class="space-y-4 py-8 text-center">
                <div
                    class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                >
                    <CheckCircle2
                        class="h-12 w-12 text-green-600 dark:text-green-400"
                    />
                </div>
                <div>
                    <h1
                        class="text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Payment Successful!
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Thank you for your order. We've received your payment.
                    </p>
                </div>
            </div>

            <!-- Order Details -->
            <div v-if="order" class="grid gap-6 md:grid-cols-3">
                <!-- Main Order Info -->
                <div class="space-y-6 md:col-span-2">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Order Summary</CardTitle>
                                <Badge class="bg-green-600">Paid</Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Barcode Section -->
                            <div
                                class="flex flex-col items-center justify-center rounded-xl border bg-white py-4"
                            >
                                <p
                                    class="mb-2 text-xs font-medium tracking-widest text-gray-400 uppercase"
                                >
                                    Transaction Barcode
                                </p>
                                <svg ref="barcodeRef" class="max-w-full"></svg>
                            </div>

                            <!-- Transaction reference & date -->
                            <div class="grid grid-cols-2 gap-4 border-b pb-4">
                                <div>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        Transaction Reference
                                    </p>
                                    <p class="font-mono text-sm font-semibold">
                                        {{ order.transaction_reference }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        Order Date
                                    </p>
                                    <p class="font-semibold">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Shop Details -->
                            <div class="space-y-3">
                                <h3 class="text-lg font-semibold">
                                    Shop Information
                                </h3>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <p
                                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <Package class="h-4 w-4" /> Shop
                                            Name
                                        </p>
                                        <p class="ml-6 font-medium">
                                            {{ order.shop_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <User class="h-4 w-4" /> Owner
                                        </p>
                                        <p class="ml-6 font-medium">
                                            {{ order.owner_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <Phone class="h-4 w-4" /> Contact
                                        </p>
                                        <p class="ml-6 font-medium">
                                            {{ order.phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <MapPin class="h-4 w-4" /> Email
                                        </p>
                                        <p class="ml-6 font-medium">
                                            {{ order.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="border-t pt-4">
                                <p
                                    class="mb-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <MapPin class="h-4 w-4" /> Shop Address
                                </p>
                                <p class="ml-6 font-medium">
                                    <template v-if="order.block_street"
                                        >{{ order.block_street }},
                                    </template>
                                    {{ order.barangay }}<br />
                                    {{ order.municipality
                                    }}<template v-if="order.postal_code"
                                        >, {{ order.postal_code }}</template
                                    >
                                </p>
                            </div>

                            <!-- Plan + Included Modules -->
                            <div class="border-t pt-4">
                                <h3 class="mb-3 text-lg font-semibold">
                                    Plan Purchased
                                </h3>

                                <!-- Plan header -->
                                <div
                                    class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
                                >
                                    <div
                                        class="mb-3 flex items-center justify-between"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600"
                                            >
                                                <BadgeCheck
                                                    class="h-5 w-5 text-white"
                                                />
                                            </div>
                                            <div>
                                                <p
                                                    class="text-base font-bold text-blue-900 dark:text-blue-100"
                                                >
                                                    {{ order.plan_name }} Plan
                                                </p>
                                                <p
                                                    class="text-xs text-blue-600 dark:text-blue-400"
                                                >
                                                    {{
                                                        billingLabel(
                                                            order.billing_months,
                                                        )
                                                    }}
                                                    subscription
                                                </p>
                                            </div>
                                        </div>
                                        <p
                                            class="text-lg font-bold text-green-600"
                                        >
                                            ₱{{
                                                parseFloat(
                                                    order.total_price,
                                                ).toLocaleString('en-PH', {
                                                    minimumFractionDigits: 2,
                                                })
                                            }}
                                        </p>
                                    </div>

                                    <!-- Included sub-modules -->
                                    <div
                                        class="mt-1 border-t border-blue-200 pt-3 dark:border-blue-700"
                                    >
                                        <p
                                            class="mb-2 text-xs font-semibold tracking-widest text-blue-700 uppercase dark:text-blue-300"
                                        >
                                            Included Modules
                                        </p>
                                        <div
                                            class="grid grid-cols-1 gap-2 sm:grid-cols-2"
                                        >
                                            <div
                                                v-for="(
                                                    module, index
                                                ) in order.modules"
                                                :key="index"
                                                class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200"
                                            >
                                                <component
                                                    :is="
                                                        getModuleIcon(
                                                            module.name,
                                                        )
                                                    "
                                                    class="h-4 w-4 flex-shrink-0 text-blue-500"
                                                />
                                                <span>{{ module.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t pt-4">
                                <div
                                    class="flex items-center justify-between text-xl"
                                >
                                    <span class="font-semibold"
                                        >Total Paid</span
                                    >
                                    <span class="font-bold text-green-600"
                                        >₱{{
                                            parseFloat(
                                                order.total_price,
                                            ).toLocaleString('en-PH', {
                                                minimumFractionDigits: 2,
                                            })
                                        }}</span
                                    >
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
                            <CardTitle class="text-lg"
                                >Payment Details</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <CreditCard class="h-4 w-4" /> Payment
                                    Method
                                </p>
                                <p class="ml-6 font-medium capitalize">
                                    {{ order.payment_method ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <BadgeCheck class="h-4 w-4" /> Status
                                </p>
                                <div class="ml-6">
                                    <Badge class="bg-green-600 capitalize">{{
                                        order.status
                                    }}</Badge>
                                </div>
                            </div>
                            <div>
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <Package class="h-4 w-4" /> Plan
                                </p>
                                <p class="ml-6 font-medium">
                                    {{ order.plan_name }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <CalendarDays class="h-4 w-4" /> Billing
                                    Period
                                </p>
                                <p class="ml-6 font-medium">
                                    {{ billingLabel(order.billing_months) }}
                                </p>
                            </div>
                            <div v-if="order.expires_at">
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <Clock class="h-4 w-4" /> Expires At
                                </p>
                                <p class="ml-6 text-sm font-medium">
                                    {{ formatDate(order.expires_at) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <Clock class="h-4 w-4" /> Paid At
                                </p>
                                <p class="ml-6 text-sm font-medium">
                                    {{ formatDate(order.created_at) }}
                                </p>
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
                                <div
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30"
                                >
                                    <Clock
                                        class="h-4 w-4 text-orange-600 dark:text-orange-400"
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-medium">
                                        Wait for admin approval
                                    </p>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-400"
                                    >
                                        Your plan will be automatically
                                        activated when admin approves your
                                        order. Thank you!
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardContent class="space-y-3 pt-6">
                            <Button
                                @click="downloadReceipt"
                                class="w-full"
                                :disabled="isLoading"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                {{
                                    isLoading
                                        ? 'Generating PDF...'
                                        : 'Download Receipt (PDF)'
                                }}
                            </Button>
                            <Link
                                :href="'/shop/dashboard'"
                                @click="unlockDashboard"
                            >
                                <Button variant="outline" class="w-full">
                                    <Home class="mr-2 h-4 w-4" />
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
                    <div
                        class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                    >
                        <CheckCircle2
                            class="h-10 w-10 text-green-600 dark:text-green-400"
                        />
                    </div>
                    <h2 class="mb-2 text-xl font-semibold">Payment Received</h2>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        Your payment was successful. Check your email for
                        details.
                    </p>
                    <Link :href="'/shop/dashboard'" @click="unlockDashboard">
                        <Button>
                            <Home class="mr-2 h-4 w-4" />
                            Return to Dashboard
                        </Button>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
