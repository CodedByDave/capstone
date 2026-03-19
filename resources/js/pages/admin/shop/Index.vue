<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Eye, Pencil, Trash2, Store, User, CheckCircle, ShieldOff, ShieldCheck } from 'lucide-vue-next'

import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog'

const { shops, stats } = defineProps<{
    shops: Array<{
        id: number
        shop_name: string
        branch_name: string | null
        phone: string
        block_street: string
        municipality: string
        barangay: string
        postal_code: string
        status: string
        plan: string | null
        disable_reason: string | null
        created_at: string
        owner: { name: string; email: string }
    }>
    stats: { today: number; total: number; active: number }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
]

const searchQuery = ref('')
const statusFilter = ref('all')

// Disable dialog state
const disableDialogOpen = ref(false)
const selectedShop = ref<typeof shops[0] | null>(null)
const disableReason = ref('')

const filteredShops = computed(() =>
    shops.filter(shop => {
        const matchesSearch =
            shop.shop_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.owner.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.owner.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.phone.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesStatus =
            statusFilter.value === 'all' || shop.status === statusFilter.value

        return matchesSearch && matchesStatus
    })
)

const planLabel: Record<string, { label: string; class: string }> = {
    basic:      { label: 'Basic',      class: 'bg-slate-100 text-slate-700' },
    standard:   { label: 'Standard',   class: 'bg-blue-100 text-blue-700' },
    premium:    { label: 'Premium',    class: 'bg-purple-100 text-purple-700' },
    enterprise: { label: 'Enterprise', class: 'bg-amber-100 text-amber-700' },
}

function getPlan(plan: string | null) {
    if (!plan) return { label: '—', class: 'bg-gray-100 text-gray-400' }
    return planLabel[plan] ?? { label: plan, class: 'bg-gray-100 text-gray-500' }
}

function viewShop(id: number) {
    router.visit(`/admin/shop/${id}`)
}

function editShop(id: number) {
    router.visit(`/admin/shop/${id}/edit`)
}

function archiveShop(id: number) {
    router.delete(`/admin/shop/${id}`)
}

function openDisableDialog(shop: typeof shops[0]) {
    selectedShop.value = shop
    disableReason.value = ''
    disableDialogOpen.value = true
}

function confirmDisable() {
    if (!selectedShop.value) return
    router.post(`/admin/shop/${selectedShop.value.id}/disable`, {
        reason: disableReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            disableDialogOpen.value = false
            selectedShop.value = null
            disableReason.value = ''
        },
    })
}

function enableShop(id: number) {
    router.post(`/admin/shop/${id}/enable`, {}, { preserveScroll: true })
}
</script>

<template>
    <Head title="Shop Management" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">

        <!-- STATS -->
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Registered Today</CardTitle>
                    <Store class="h-5 w-5 text-blue-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">{{ stats.today }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Total Shops</CardTitle>
                    <User class="h-5 w-5 text-purple-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-purple-600">{{ stats.total }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Active Shops</CardTitle>
                    <CheckCircle class="h-5 w-5 text-green-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- TABLE -->
        <Card>
            <CardHeader class="flex justify-between items-center">
                <CardTitle>Shop List</CardTitle>
                <div class="flex gap-2">
                    <Input v-model="searchQuery" placeholder="Search..." class="max-w-xs" />
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-32">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="disabled">Disabled</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Shop Name</TableHead>
                            <TableHead>Owner</TableHead>
                            <TableHead>Phone</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Municipality</TableHead>
                            <TableHead>Barangay</TableHead>
                            <TableHead>Plan</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-center">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="shop in filteredShops"
                            :key="shop.id"
                            :class="{ 'opacity-60 bg-red-50': shop.status === 'disabled' }"
                        >
                            <TableCell class="font-medium">{{ shop.shop_name }}</TableCell>
                            <TableCell>{{ shop.owner.name }}</TableCell>
                            <TableCell>{{ shop.phone }}</TableCell>
                            <TableCell>{{ shop.owner.email }}</TableCell>
                            <TableCell>{{ shop.municipality }}</TableCell>
                            <TableCell>{{ shop.barangay }}</TableCell>

                            <!-- Plan Badge -->
                            <TableCell>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getPlan(shop.plan).class"
                                >
                                    {{ getPlan(shop.plan).label }}
                                </span>
                            </TableCell>

                            <!-- Status Badge -->
                            <TableCell>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full text-white"
                                    :class="{
                                        'bg-green-500': shop.status === 'active',
                                        'bg-red-500':   shop.status === 'inactive',
                                        'bg-yellow-500': shop.status === 'pending',
                                        'bg-gray-500':  shop.status === 'disabled',
                                    }"
                                >
                                    {{ shop.status }}
                                </span>
                            </TableCell>

                            <!-- Actions -->
                            <TableCell class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Button size="icon" variant="ghost" @click="viewShop(shop.id)">
                                        <Eye class="h-4 w-4 text-blue-500" />
                                    </Button>

                                    <Button size="icon" variant="ghost" @click="editShop(shop.id)">
                                        <Pencil class="h-4 w-4 text-green-500" />
                                    </Button>

                                    <!-- Archive -->
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button size="icon" variant="ghost">
                                                <Trash2 class="h-4 w-4 text-red-600" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Archive Shop</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Are you sure you want to archive
                                                    <strong>{{ shop.shop_name }}</strong>?
                                                    This action can be undone later.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                                    @click="archiveShop(shop.id)"
                                                >
                                                    Archive
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>

                                    <!-- Disable / Enable Toggle -->
                                    <template v-if="shop.status !== 'disabled'">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            title="Disable Shop"
                                            @click="openDisableDialog(shop)"
                                        >
                                            <ShieldOff class="h-4 w-4 text-orange-500" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <AlertDialog>
                                            <AlertDialogTrigger asChild>
                                                <Button size="icon" variant="ghost" title="Enable Shop">
                                                    <ShieldCheck class="h-4 w-4 text-green-600" />
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Enable Shop</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        Re-enable <strong>{{ shop.shop_name }}</strong>? They will regain full access to the system.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction @click="enableShop(shop.id)">
                                                        Enable
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </template>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Disable Shop Dialog (with reason) -->
        <AlertDialog :open="disableDialogOpen" @update:open="disableDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="flex items-center gap-2 text-orange-600">
                        <ShieldOff class="h-5 w-5" />
                        Disable Shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        You are about to disable <strong>{{ selectedShop?.shop_name }}</strong>.
                        The shop owner will lose access to the system. Please provide a reason.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <div class="mt-2">
                    <label class="text-sm font-medium">Reason for disabling</label>
                    <Textarea
                        v-model="disableReason"
                        class="mt-1"
                        placeholder="e.g. Violation of terms of service, fraudulent activity, etc."
                        rows="3"
                    />
                </div>

                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel @click="disableDialogOpen = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-orange-600 text-white hover:bg-orange-700"
                        :disabled="!disableReason.trim()"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
